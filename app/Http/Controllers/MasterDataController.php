<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\StudentKpi;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        $semester = Semester::active();

        return view('master-data.index', [
            'semester' => $semester,
            'academicYears' => Semester::query()->select('academic_year')->distinct()->orderByDesc('academic_year')->pluck('academic_year'),
            'classes' => SchoolClass::with('homeroomTeacher')->withCount(['enrollments', 'assignments'])->orderByDesc('academic_year')->orderBy('name')->get(),
            'students' => Student::with(['user', 'enrollments.schoolClass'])->orderBy('name')->paginate(50),
            'allStudents' => Student::orderBy('name')->get(['id', 'nis', 'name', 'status']),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
            'teacherProfiles' => Teacher::with(['user', 'assignments' => fn ($query) => $query->with(['schoolClass', 'subject'])->where('semester_id', $semester?->id)])
                ->orderBy('name')->get(),
            'guardians' => Guardian::with(['user', 'students'])->orderBy('name')->get(),
            'assignments' => $semester
                ? TeachingAssignment::with(['teacher', 'schoolClass', 'subject'])->where('semester_id', $semester->id)->orderBy('class_id')->get()
                : collect(),
        ]);
    }

    public function storeClass(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'grade_level' => ['required', 'integer', 'between:1,12'],
            'academic_year' => ['required', 'exists:semesters,academic_year'],
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
        ]);

        $request->validate([
            'name' => [Rule::unique('school_classes')->where('academic_year', $data['academic_year'])],
        ], ['name.unique' => 'Nama kelas tersebut sudah digunakan pada tahun ajaran ini.']);

        $class = SchoolClass::create($data);
        $audit->log('school_class.created', $class, null, $class->only(['name', 'grade_level', 'academic_year', 'homeroom_teacher_id']));

        return back()->with('status', "Kelas {$class->name} berhasil ditambahkan.");
    }

    public function updateClass(Request $request, SchoolClass $class, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30', Rule::unique('school_classes')->where('academic_year', $class->academic_year)->ignore($class->id)],
            'grade_level' => ['required', 'integer', 'between:1,12'],
            'homeroom_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
        ]);
        $old = $class->only(['name', 'grade_level', 'homeroom_teacher_id']);
        $class->update($data);
        $audit->log('school_class.updated', $class, $old, $class->only(array_keys($old)));

        return back()->with('status', "Kelas {$class->name} berhasil diperbarui.");
    }

    public function destroyClass(SchoolClass $class, AuditLogger $audit): RedirectResponse
    {
        abort_if($class->enrollments()->exists() || $class->assignments()->exists() || $class->students()->exists()
            || StudentKpi::where('class_id', $class->id)->exists(), 409,
            'Kelas tidak dapat dihapus karena masih memiliki siswa atau penugasan.');
        $old = $class->only(['name', 'grade_level', 'academic_year', 'homeroom_teacher_id']);
        $class->delete();
        $audit->log('school_class.deleted', $class, $old, null);

        return back()->with('status', 'Kelas kosong berhasil dihapus.');
    }

    public function storeStudent(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'username' => ['nullable', 'required_with:password', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'required_with:username', 'string', 'min:12', 'confirmed'],
        ]);

        $class = filled($data['class_id'] ?? null) ? SchoolClass::findOrFail($data['class_id']) : null;
        if ($class) {
            $semester = Semester::where('academic_year', $class->academic_year)->first();
            abort_unless($semester, 422, 'Tahun ajaran kelas belum terdaftar sebagai semester.');
        }

        $student = DB::transaction(function () use ($data, $class, $audit): Student {
            $student = Student::create(collect($data)->except(['class_id', 'username', 'email', 'password'])->all());
            if ($class) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'academic_year' => $class->academic_year,
                ]);
            }
            $accountSummary = null;
            if (! empty($data['username'])) {
                $user = User::create([
                    'name' => $student->name, 'username' => $data['username'], 'email' => $data['email'] ?? null,
                    'password' => $data['password'], 'role' => 'siswa', 'is_active' => $student->status === 'aktif',
                ]);
                $user->assignRole('siswa');
                $student->update(['user_id' => $user->id]);
                $accountSummary = ['username' => $user->username, 'email' => $user->email];
            }
            $audit->log('student.created', $student, null, [
                ...$student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'status']),
                'class_id' => $class?->id, 'account' => $accountSummary,
            ]);

            return $student;
        });

        return back()->with('status', $class
            ? "Siswa {$student->name} berhasil ditambahkan ke kelas {$class->name}."
            : "Profil siswa {$student->name} berhasil dibuat. Tempatkan siswa melalui menu Wali Kelas.");
    }

    public function updateStudent(Request $request, Student $student, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:30', Rule::unique('students', 'nisn')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($student->user_id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->user_id)],
            'password' => ['nullable', Rule::requiredIf(! $student->user_id && filled($request->input('username'))), 'string', 'min:12', 'confirmed'],
            'account_active' => ['nullable', 'boolean'],
        ]);
        $class = filled($data['class_id'] ?? null) ? SchoolClass::findOrFail($data['class_id']) : null;
        $enrollment = $class ? $student->enrollments()->where('academic_year', $class->academic_year)->first() : null;
        $activeSemester = Semester::active();
        $activeEnrollment = $activeSemester
            ? $student->enrollments()->where('academic_year', $activeSemester->academic_year)->first()
            : null;

        DB::transaction(function () use ($student, $data, $class, $enrollment, $activeEnrollment, $audit): void {
            $old = $student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'status']);
            $old['class_id'] = $enrollment?->class_id ?? $activeEnrollment?->class_id;
            $user = $student->user;
            $old['account'] = $user ? ['username' => $user->username, 'email' => $user->email, 'is_active' => $user->is_active] : null;
            $student->update(collect($data)->except(['class_id', 'username', 'email', 'password', 'account_active'])->all());
            if ($class) {
                if ($enrollment) {
                    $enrollment->update(['class_id' => $class->id]);
                } else {
                    Enrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'academic_year' => $class->academic_year]);
                }
            }
            $usernameProvided = filled($data['username'] ?? null);
            if ($user || $usernameProvided) {
                if (! $user) {
                    $user = User::create([
                        'name' => $student->name, 'username' => $data['username'], 'email' => $data['email'] ?? null,
                        'password' => $data['password'], 'role' => 'siswa', 'is_active' => $student->status === 'aktif',
                    ]);
                    $user->assignRole('siswa');
                    $student->update(['user_id' => $user->id]);
                } else {
                    $userData = ['name' => $student->name, 'is_active' => $student->status === 'aktif'
                        && (array_key_exists('account_active', $data) ? (bool) $data['account_active'] : $user->is_active)];
                    if ($usernameProvided) {
                        $userData['username'] = $data['username'];
                    }
                    if (array_key_exists('email', $data)) {
                        $userData['email'] = $data['email'] ?: null;
                    }
                    if (! empty($data['password'])) {
                        $userData['password'] = $data['password'];
                    }
                    $user->update($userData);
                }
            }
            $new = $student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'status']);
            $new['class_id'] = $class?->id ?? $activeEnrollment?->class_id;
            $new['account'] = $user ? ['username' => $user->username, 'email' => $user->email, 'is_active' => $user->is_active] : null;
            $audit->log('student.updated', $student, $old, $new);
        });

        return back()->with('status', "Data siswa {$student->name} berhasil diperbarui.");
    }

    public function destroyStudent(Student $student, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($student, $audit): void {
            $old = $student->only(['nis', 'nisn', 'name', 'gender', 'status']);
            $student->update(['status' => 'keluar']);
            if ($student->user) {
                $student->user->update(['is_active' => false]);
            }
            $student->delete();
            $audit->log('student.archived', $student, $old, null);
        });

        return back()->with('status', "Data siswa {$student->name} diarsipkan; riwayat akademiknya tetap tersimpan.");
    }

    public function storeGuardian(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', Rule::exists('students', 'id')->whereNull('deleted_at')],
            'relationship' => ['required', 'string', 'max:30'],
        ]);

        $guardian = DB::transaction(function () use ($data, $audit): Guardian {
            $user = User::create([
                'name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null, 'password' => $data['password'], 'role' => 'orangtua', 'is_active' => true,
            ]);
            $user->assignRole('orangtua');
            $guardian = Guardian::create(['user_id' => $user->id, 'name' => $data['name'], 'phone' => $data['phone'] ?? null]);
            $guardian->students()->syncWithPivotValues($data['student_ids'], ['relationship' => $data['relationship']]);
            $audit->log('guardian.created', $guardian, null, [
                'name' => $guardian->name, 'phone' => $guardian->phone, 'username' => $user->username,
                'student_ids' => array_map('intval', $data['student_ids']),
            ]);

            return $guardian;
        });

        return back()->with('status', "Akun orang tua/wali {$guardian->name} berhasil dibuat.");
    }

    public function updateGuardian(Request $request, Guardian $guardian, AuditLogger $audit): RedirectResponse
    {
        $user = $guardian->user;
        abort_unless($user, 422, 'Profil orang tua/wali ini belum memiliki akun pengguna.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', Rule::exists('students', 'id')->whereNull('deleted_at')],
            'relationship' => ['required', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($guardian, $user, $data, $audit): void {
            $old = ['name' => $guardian->name, 'phone' => $guardian->phone,
                'username' => $user->username, 'email' => $user->email,
                'student_ids' => $guardian->students()->pluck('students.id')->all()];
            $guardian->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);
            $userData = ['name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null, 'is_active' => (bool) $data['is_active']];
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $user->update($userData);
            $guardian->students()->syncWithPivotValues($data['student_ids'], ['relationship' => $data['relationship']]);
            $audit->log('guardian.updated', $guardian, $old, [
                'name' => $guardian->name, 'phone' => $guardian->phone, 'username' => $user->username,
                'email' => $user->email, 'is_active' => $user->is_active,
                'student_ids' => array_map('intval', $data['student_ids']),
            ]);
        });

        return back()->with('status', "Data orang tua/wali {$guardian->name} berhasil diperbarui.");
    }

    public function destroyGuardian(Guardian $guardian, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($guardian, $audit): void {
            $user = $guardian->user;
            $old = ['name' => $guardian->name, 'phone' => $guardian->phone,
                'username' => $user?->username, 'student_ids' => $guardian->students()->pluck('students.id')->all()];
            $user?->update(['is_active' => false]);
            $guardian->delete();
            $audit->log('guardian.archived', $guardian, $old, null);
        });

        return back()->with('status', "Akun orang tua/wali {$guardian->name} diarsipkan; hubungan anak tetap tersimpan.");
    }

    public function storeSubject(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:255', 'unique:subjects,name'],
        ]);

        $subject = Subject::create([...$data, 'is_active' => true]);
        $audit->log('subject.created', $subject, null, $subject->only(['code', 'name', 'is_active']));

        return back()->with('status', "Mata pelajaran {$subject->name} berhasil ditambahkan.");
    }

    public function updateSubject(Request $request, Subject $subject, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'is_active' => ['required', 'boolean'],
        ]);
        $old = $subject->only(['code', 'name', 'is_active']);
        $subject->update([...$data, 'is_active' => (bool) $data['is_active']]);
        $audit->log('subject.updated', $subject, $old, $subject->only(array_keys($old)));

        return back()->with('status', "Mata pelajaran {$subject->name} berhasil diperbarui.");
    }

    public function storeTeacher(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $teacher = DB::transaction(function () use ($data, $audit): Teacher {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => 'guru',
                'is_active' => true,
            ]);
            $user->assignRole('guru');

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => true,
            ]);

            $audit->log('teacher.created', $teacher, null, [
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'username' => $user->username,
                'email' => $user->email,
            ]);

            return $teacher;
        });

        return back()->with('status', "Akun guru {$teacher->name} berhasil dibuat.");
    }

    public function updateTeacher(Request $request, Teacher $teacher, AuditLogger $audit): RedirectResponse
    {
        $user = $teacher->user;
        abort_unless($user, 422, 'Profil guru ini belum memiliki akun pengguna.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($teacher, $user, $data, $audit): void {
            $old = ['teacher' => $teacher->only(['name', 'nip', 'phone', 'is_active']),
                'user' => $user->only(['name', 'username', 'email', 'phone', 'is_active'])];
            $teacher->update(['name' => $data['name'], 'nip' => $data['nip'] ?? null,
                'phone' => $data['phone'] ?? null, 'is_active' => (bool) $data['is_active']]);
            $userData = ['name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null, 'is_active' => (bool) $data['is_active']];
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $user->update($userData);
            $audit->log('teacher.updated', $teacher, $old, [
                'teacher' => $teacher->only(['name', 'nip', 'phone', 'is_active']),
                'user' => $user->only(['name', 'username', 'email', 'phone', 'is_active']),
            ]);
        });

        return back()->with('status', "Data guru {$teacher->name} berhasil diperbarui.");
    }

    public function storeAssignment(Request $request, AuditLogger $audit): RedirectResponse
    {
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu sebelum membuat penugasan.');

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')->where('is_active', true)],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')->where('academic_year', $semester->academic_year)],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')->where('is_active', true)],
        ]);

        $assignment = DB::transaction(function () use ($data, $semester, $audit): TeachingAssignment {
            $assignment = TeachingAssignment::firstOrNew([
                'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'semester_id' => $semester->id,
            ]);
            $oldTeacherId = $assignment->exists ? $assignment->teacher_id : null;
            $assignment->teacher_id = $data['teacher_id'];
            $assignment->save();

            $audit->log($oldTeacherId ? 'teaching_assignment.reassigned' : 'teaching_assignment.created', $assignment,
                $oldTeacherId ? ['teacher_id' => $oldTeacherId] : null,
                ['teacher_id' => $assignment->teacher_id, 'class_id' => $assignment->class_id,
                    'subject_id' => $assignment->subject_id, 'semester_id' => $assignment->semester_id]);

            return $assignment;
        });

        return back()->with('status', 'Penugasan guru berhasil disimpan.');
    }

    public function destroyAssignment(TeachingAssignment $assignment, AuditLogger $audit): RedirectResponse
    {
        $old = $assignment->only(['teacher_id', 'class_id', 'subject_id', 'semester_id']);
        $audit->log('teaching_assignment.deleted', $assignment, $old, null);
        $assignment->delete();

        return back()->with('status', 'Penugasan guru berhasil dihapus. Riwayat nilai dan absensi tetap tersimpan.');
    }
}
