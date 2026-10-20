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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(Request $request, ?string $section = null): View
    {
        $sectionLabels = [
            'kelas' => 'Data Kelas',
            'siswa' => 'Data Siswa',
            'guru' => 'Data Guru',
            'mapel' => 'Mata Pelajaran',
            'penugasan' => 'Penugasan Guru',
        ];
        abort_if($section !== null && ! array_key_exists($section, $sectionLabels), 404);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'entry_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
        ]);
        $search = trim($filters['q'] ?? '');
        $entryYear = $filters['entry_year'] ?? null;
        $dataTab = $section !== null ? $request->query('tab', 'data') : null;
        abort_if($dataTab !== null && ! in_array($dataTab, ['data', 'upload'], true), 404);
        $semester = Semester::active();

        return view('master-data.index', [
            'section' => $section,
            'sectionLabels' => $sectionLabels,
            'dataTab' => $dataTab,
            'search' => $search,
            'entryYear' => $entryYear ? (int) $entryYear : null,
            'semester' => $semester,
            'academicYears' => Semester::query()->select('academic_year')->distinct()->orderByDesc('academic_year')->pluck('academic_year'),
            'classes' => SchoolClass::with('homeroomTeacher')->withCount(['enrollments', 'assignments'])
                ->when($search !== '' && $section === 'kelas', fn ($query) => $query->where(fn ($classQuery) => $classQuery
                    ->where('name', 'like', "%{$search}%")->orWhere('academic_year', 'like', "%{$search}%")))
                ->orderByDesc('academic_year')->orderBy('name')->get(),
            'students' => Student::with(['user', 'enrollments.schoolClass', 'guardians.user'])
                ->when($search !== '' && $section === 'siswa', fn ($query) => $query->where(fn ($studentQuery) => $studentQuery
                    ->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")))
                ->when($entryYear && $section === 'siswa', fn ($query) => $query->whereYear('registration_date', $entryYear))
                ->orderByDesc('registration_date')->orderBy('name')->paginate(50)->withQueryString(),
            'registrationYears' => Student::query()->whereNotNull('registration_date')
                ->selectRaw('YEAR(registration_date) as registration_year')->distinct()
                ->orderByDesc('registration_year')->pluck('registration_year'),
            'subjects' => Subject::when($search !== '' && $section === 'mapel', fn ($query) => $query->where(fn ($subjectQuery) => $subjectQuery
                    ->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
                ->orderBy('name')->get(),
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
            'teacherProfiles' => Teacher::with(['user', 'assignments' => fn ($query) => $query->with(['schoolClass', 'subject'])->where('semester_id', $semester?->id)])
                ->when($search !== '' && $section === 'guru', fn ($query) => $query->where(fn ($teacherQuery) => $teacherQuery
                    ->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('username', 'like', "%{$search}%"))))
                ->orderBy('name')->get(),
            'assignments' => $semester
                ? TeachingAssignment::with(['teacher', 'schoolClass', 'subject'])->where('semester_id', $semester->id)
                    ->when($search !== '' && $section === 'penugasan', fn ($query) => $query->where(fn ($searchQuery) => $searchQuery
                        ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"))))
                    ->orderBy('class_id')->get()
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

    public function editClass(SchoolClass $class): View
    {
        return view('master-data.record-edit', ['type' => 'kelas', 'record' => $class->load('homeroomTeacher'), 'teachers' => Teacher::where('is_active', true)->orderBy('name')->get()]);
    }

    public function destroyClass(SchoolClass $class, AuditLogger $audit): RedirectResponse
    {
        abort_if($class->enrollments()->exists() || $class->assignments()->exists() || $class->students()->exists()
            || StudentKpi::where('class_id', $class->id)->exists(), 409,
            'Kelas tidak dapat dihapus karena masih memiliki siswa atau penugasan.');
        $old = $class->only(['name', 'grade_level', 'academic_year', 'homeroom_teacher_id']);
        $class->delete();
        $audit->log('school_class.deleted', $class, $old, null);

        return redirect()->route('master-data.section', ['section' => 'kelas', 'tab' => 'data'])
            ->with('status', 'Kelas kosong berhasil dihapus.');
    }

    public function exportClasses(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');
        $classes = SchoolClass::with('homeroomTeacher')->withCount(['enrollments', 'assignments'])
            ->when($search !== '', fn ($query) => $query->where(fn ($classQuery) => $classQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('academic_year', 'like', "%{$search}%")))
            ->orderByDesc('academic_year')->orderBy('name')->get();

        return response()->streamDownload(function () use ($classes): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nama kelas', 'Tingkat', 'Tahun ajaran', 'Wali kelas', 'Jumlah siswa', 'Jumlah penugasan guru']);
            foreach ($classes as $class) {
                $row = [$class->name, $class->grade_level, $class->academic_year, $class->homeroomTeacher?->name, $class->enrollments_count, $class->assignments_count];
                fputcsv($output, array_map(static function ($value): string {
                    $value = (string) ($value ?? '');
                    return preg_match('/^[=+@\-\t\r]/', $value) ? "'".$value : $value;
                }, $row));
            }
            fclose($output);
        }, 'data-kelas-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportSubjects(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');
        $subjects = Subject::when($search !== '', fn ($query) => $query->where(fn ($subjectQuery) => $subjectQuery
            ->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->orderBy('name')->get();

        return response()->streamDownload(function () use ($subjects): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Kode', 'Mata pelajaran', 'Status']);
            foreach ($subjects as $subject) {
                fputcsv($output, [$subject->code, $subject->name, $subject->is_active ? 'Aktif' : 'Nonaktif']);
            }
            fclose($output);
        }, 'data-mapel-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportTeachers(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');
        $teachers = Teacher::with(['user', 'assignments.schoolClass', 'assignments.subject'])
            ->when($search !== '', fn ($query) => $query->where(fn ($teacherQuery) => $teacherQuery
                ->where('name', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('username', 'like', "%{$search}%"))))
            ->orderBy('name')->get();

        return response()->streamDownload(function () use ($teachers): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Nama guru', 'NIP', 'Username', 'Email', 'Nomor HP', 'Status', 'Penugasan']);
            foreach ($teachers as $teacher) {
                $assignments = $teacher->assignments->map(fn ($assignment) => $assignment->subject->name.' - '.$assignment->schoolClass->name)->implode('; ');
                fputcsv($output, [$teacher->name, $teacher->nip, $teacher->user?->username, $teacher->user?->email,
                    $teacher->phone, $teacher->is_active ? 'Aktif' : 'Nonaktif', $assignments]);
            }
            fclose($output);
        }, 'data-guru-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportAssignments(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['q'] ?? '');
        $semester = Semester::active();
        $assignments = $semester
            ? TeachingAssignment::with(['teacher', 'schoolClass', 'subject'])->where('semester_id', $semester->id)
                ->when($search !== '', fn ($query) => $query->where(fn ($searchQuery) => $searchQuery
                    ->whereHas('teacher', fn ($teacherQuery) => $teacherQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"))))
                ->orderBy('class_id')->get()
            : collect();

        return response()->streamDownload(function () use ($assignments, $semester): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Semester', 'Guru', 'Mata pelajaran', 'Kelas', 'Tahun ajaran']);
            foreach ($assignments as $assignment) {
                fputcsv($output, [$semester?->label(), $assignment->teacher->name, $assignment->subject->name,
                    $assignment->schoolClass->name, $assignment->schoolClass->academic_year]);
            }
            fclose($output);
        }, 'data-penugasan-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function masterImportTemplate(string $section): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = match ($section) {
            'kelas' => ['nama_kelas', 'tingkat', 'tahun_ajaran'],
            'mapel' => ['kode', 'nama_mapel'],
            'guru' => ['nama', 'username', 'email', 'nip', 'no_hp', 'password_awal'],
            'penugasan' => ['username_guru', 'nama_kelas', 'kode_mapel'],
            default => abort(404),
        };

        return response()->streamDownload(function () use ($headers): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            fclose($output);
        }, 'template-'.$section.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importMasterData(Request $request, string $section, AuditLogger $audit): RedirectResponse
    {
        abort_unless(in_array($section, ['kelas', 'mapel', 'guru', 'penugasan'], true), 404);
        $input = $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $handle = fopen($input['file']->getRealPath(), 'rb');
        $headers = $handle ? fgetcsv($handle) : false;
        if (! $handle || ! is_array($headers)) {
            if (is_resource($handle)) fclose($handle);
            throw ValidationException::withMessages(['file' => 'File CSV kosong atau tidak dapat dibaca.']);
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
        $required = match ($section) {
            'kelas' => ['nama_kelas', 'tingkat', 'tahun_ajaran'],
            'mapel' => ['nama_mapel'],
            'guru' => ['nama', 'username', 'password_awal'],
            'penugasan' => ['username_guru', 'nama_kelas', 'kode_mapel'],
        };
        $missing = array_diff($required, $headers);
        if ($missing) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'Kolom wajib belum lengkap: '.implode(', ', $missing).'.']);
        }
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) continue;
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw ValidationException::withMessages(['file' => 'Jumlah kolom pada salah satu baris tidak sesuai template.']);
            }
            $rows[] = array_combine($headers, $values);
            if (count($rows) > 2000) {
                fclose($handle);
                throw ValidationException::withMessages(['file' => 'Maksimal 2.000 baris per unggahan.']);
            }
        }
        fclose($handle);
        if ($rows === []) throw ValidationException::withMessages(['file' => 'File belum berisi data.']);
        $semester = $section === 'penugasan' ? Semester::active() : null;
        if ($section === 'penugasan' && ! $semester) throw ValidationException::withMessages(['file' => 'Aktifkan semester sebelum mengimpor penugasan.']);

        DB::transaction(function () use ($rows, $section, $semester, $audit): void {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                try {
                    if ($section === 'kelas') {
                        $data = ['name' => trim($row['nama_kelas']), 'grade_level' => trim($row['tingkat']), 'academic_year' => trim($row['tahun_ajaran'])];
                        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:30'], 'grade_level' => ['required', 'integer', 'between:1,12'], 'academic_year' => ['required', 'exists:semesters,academic_year']]);
                        if ($validator->fails()) throw new \RuntimeException(implode(' ', $validator->errors()->all()));
                        if (SchoolClass::where('name', $data['name'])->where('academic_year', $data['academic_year'])->exists()) throw new \RuntimeException('Nama kelas sudah ada pada tahun ajaran tersebut.');
                        $record = SchoolClass::create($data);
                        $audit->log('school_class.created', $record, null, $record->only(['name', 'grade_level', 'academic_year']));
                    } elseif ($section === 'mapel') {
                        $data = ['code' => trim($row['kode'] ?? '') ?: null, 'name' => trim($row['nama_mapel'])];
                        $validator = Validator::make($data, ['code' => ['nullable', 'string', 'max:20', 'unique:subjects,code'], 'name' => ['required', 'string', 'max:255', 'unique:subjects,name']]);
                        if ($validator->fails()) throw new \RuntimeException(implode(' ', $validator->errors()->all()));
                        $record = Subject::create([...$data, 'is_active' => true]);
                        $audit->log('subject.created', $record, null, $record->only(['code', 'name', 'is_active']));
                    } elseif ($section === 'guru') {
                        $data = ['name' => trim($row['nama']), 'username' => trim($row['username']), 'email' => trim($row['email'] ?? '') ?: null, 'nip' => trim($row['nip'] ?? '') ?: null, 'phone' => trim($row['no_hp'] ?? '') ?: null, 'password' => (string) ($row['password_awal'] ?? '')];
                        $validator = Validator::make($data, ['name' => ['required', 'string', 'max:255'], 'username' => ['required', 'string', 'max:255', 'unique:users,username'], 'email' => ['nullable', 'email', 'max:255', 'unique:users,email'], 'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'], 'phone' => ['nullable', 'string', 'max:20'], 'password' => ['required', 'string', 'min:12']]);
                        if ($validator->fails()) throw new \RuntimeException(implode(' ', $validator->errors()->all()));
                        $user = User::create(['name' => $data['name'], 'username' => $data['username'], 'email' => $data['email'], 'phone' => $data['phone'], 'password' => $data['password'], 'role' => 'guru', 'is_active' => true]);
                        $user->assignRole('guru');
                        $record = Teacher::create(['user_id' => $user->id, 'name' => $data['name'], 'nip' => $data['nip'], 'phone' => $data['phone'], 'is_active' => true]);
                        $audit->log('teacher.created', $record, null, ['name' => $record->name, 'nip' => $record->nip, 'username' => $user->username, 'email' => $user->email]);
                    } else {
                        $teacher = Teacher::whereHas('user', fn ($query) => $query->where('username', trim($row['username_guru'])))->where('is_active', true)->first();
                        $class = SchoolClass::where('name', trim($row['nama_kelas']))->where('academic_year', $semester->academic_year)->first();
                        $subject = Subject::where('code', trim($row['kode_mapel']))->where('is_active', true)->first();
                        if (! $teacher || ! $class || ! $subject) throw new \RuntimeException('Guru, kelas, atau kode mapel tidak ditemukan pada semester aktif.');
                        $record = TeachingAssignment::firstOrNew(['class_id' => $class->id, 'subject_id' => $subject->id, 'semester_id' => $semester->id]);
                        $oldTeacherId = $record->exists ? $record->teacher_id : null;
                        $record->teacher_id = $teacher->id;
                        $record->save();
                        $audit->log($oldTeacherId ? 'teaching_assignment.reassigned' : 'teaching_assignment.created', $record, $oldTeacherId ? ['teacher_id' => $oldTeacherId] : null, ['teacher_id' => $record->teacher_id, 'class_id' => $record->class_id, 'subject_id' => $record->subject_id, 'semester_id' => $record->semester_id]);
                    }
                } catch (\RuntimeException $exception) {
                    throw ValidationException::withMessages(['file' => "Baris {$line}: {$exception->getMessage()}"]);
                }
            }
        });

        return redirect()->route('master-data.section', ['section' => $section, 'tab' => 'upload'])->with('status', count($rows).' baris berhasil diunggah.');
    }

    public function storeStudent(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
            'nisn' => ['nullable', 'digits:10', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'digits:16', 'unique:students,nik'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'registration_date' => ['required', 'date', 'before_or_equal:today'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'father_name' => ['nullable', 'string', 'max:255', 'required_without:mother_name', 'required_with:father_phone,father_occupation'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_occupation' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:255', 'required_without:father_name', 'required_with:mother_phone,mother_occupation'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:255', 'required_with:guardian_phone,guardian_occupation'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
        ]);

        $class = filled($data['class_id'] ?? null) ? SchoolClass::findOrFail($data['class_id']) : null;
        if ($class) {
            $semester = Semester::where('academic_year', $class->academic_year)->first();
            abort_unless($semester, 422, 'Tahun ajaran kelas belum terdaftar sebagai semester.');
        }

        $photoPath = $request->file('photo')?->store('students', 'public');
        $generatedEmail = $this->generatedStudentEmail($data['nis'], $data['name']);
        $this->ensureGeneratedCredentialsAvailable($generatedEmail);
        $student = DB::transaction(function () use ($data, $class, $audit, $photoPath, $generatedEmail): Student {
            $student = Student::create(collect($data)->except([
                'class_id', 'photo', 'father_name', 'father_phone', 'father_occupation',
                'mother_name', 'mother_phone', 'mother_occupation', 'guardian_name', 'guardian_phone',
                'guardian_occupation',
            ])->all() + ['photo_path' => $photoPath]);
            if ($class) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'academic_year' => $class->academic_year,
                ]);
            }
            $user = $this->createStudentAccount($student, $generatedEmail);
            $accountSummary = ['email' => $user->email];
            $parentSummary = $this->saveStudentGuardians($student, $data);
            $audit->log('student.created', $student, null, [
                ...$student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'birth_place', 'religion', 'nik', 'photo_path', 'phone', 'registration_date', 'status']),
                'class_id' => $class?->id, 'account' => $accountSummary, 'parent' => $parentSummary,
            ]);

            return $student;
        });

        return back()->with('status', $class
            ? "Siswa {$student->name} berhasil ditambahkan ke kelas {$class->name}."
            : "Profil siswa {$student->name} berhasil dibuat. Tempatkan siswa melalui menu Wali Kelas.");
    }

    public function editStudent(Student $student): View
    {
        $student->load(['user', 'enrollments.schoolClass', 'guardians.user']);

        return view('master-data.student-edit', [
            'student' => $student,
            'classes' => SchoolClass::orderByDesc('academic_year')->orderBy('name')->get(),
        ]);
    }

    public function exportStudents(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'entry_year' => ['nullable', 'integer', 'between:1900,'.now()->year],
        ]);
        $search = trim($filters['q'] ?? '');
        $entryYear = $filters['entry_year'] ?? null;
        $students = Student::with(['user', 'enrollments.schoolClass', 'guardians'])
            ->when($search !== '', fn ($query) => $query->where(fn ($studentQuery) => $studentQuery
                ->where('name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%")))
            ->when($entryYear, fn ($query) => $query->whereYear('registration_date', $entryYear))
            ->orderByDesc('registration_date')->orderBy('name')->get();

        return response()->streamDownload(function () use ($students): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'NIS', 'NISN', 'Nama lengkap', 'Jenis kelamin', 'Tempat lahir', 'Tanggal lahir', 'Agama', 'NIK',
                'Tanggal terdaftar', 'Nomor HP siswa', 'Kelas', 'Email login', 'Status',
                'Nama ayah', 'HP ayah', 'Pekerjaan ayah', 'Nama ibu', 'HP ibu', 'Pekerjaan ibu',
                'Nama wali', 'HP wali', 'Pekerjaan wali',
            ]);

            $safeCell = static function ($value): string {
                $value = (string) ($value ?? '');
                return preg_match('/^[=+@\-\t\r]/', $value) ? "'".$value : $value;
            };

            foreach ($students as $student) {
                $enrollment = $student->enrollments->sortByDesc('academic_year')->first();
                $parents = $student->guardians->keyBy(fn ($guardian) => $guardian->pivot->relationship);
                $row = [
                    $student->nis, $student->nisn, $student->name, $student->gender, $student->birth_place,
                    $student->birth_date?->format('Y-m-d'), $student->religion, $student->nik,
                    $student->registration_date?->format('Y-m-d'), $student->phone,
                    $enrollment?->schoolClass?->name, $student->user?->email, $student->status,
                ];
                foreach (['ayah', 'ibu', 'wali'] as $relationship) {
                    $parent = $parents->get($relationship);
                    array_push($row, $parent?->name, $parent?->phone, $parent?->occupation);
                }
                fputcsv($output, array_map($safeCell, $row));
            }
            fclose($output);
        }, 'data-siswa-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function studentImportTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'tanggal_lahir', 'tempat_lahir', 'agama', 'nik',
            'tanggal_terdaftar', 'no_hp_siswa', 'nama_ayah', 'hp_ayah', 'pekerjaan_ayah',
            'nama_ibu', 'hp_ibu', 'pekerjaan_ibu', 'nama_wali', 'hp_wali', 'pekerjaan_wali',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headers);
            fclose($output);
        }, 'template-siswa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStudents(Request $request, AuditLogger $audit): RedirectResponse
    {
        $input = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $handle = fopen($input['file']->getRealPath(), 'rb');
        $headers = $handle ? fgetcsv($handle) : false;
        if (! $handle || ! is_array($headers)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw ValidationException::withMessages(['file' => 'File CSV kosong atau tidak dapat dibaca.']);
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $headers);
        $requiredHeaders = ['nis', 'nama_lengkap', 'jenis_kelamin', 'tanggal_lahir', 'nama_ayah', 'nama_ibu'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if ($missingHeaders) {
            fclose($handle);
            throw ValidationException::withMessages(['file' => 'Kolom wajib belum lengkap: '.implode(', ', $missingHeaders).'. Unduh template untuk melihat format.']);
        }

        $rows = [];
        $line = 1;
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw ValidationException::withMessages(['file' => "Jumlah kolom pada baris {$line} tidak sesuai template."]);
            }
            $rows[] = ['line' => $line, 'data' => array_combine($headers, $values)];
            if (count($rows) > 2000) {
                fclose($handle);
                throw ValidationException::withMessages(['file' => 'Maksimal 2.000 siswa dalam satu kali unggah.']);
            }
        }
        fclose($handle);

        if ($rows === []) {
            throw ValidationException::withMessages(['file' => 'File belum berisi data siswa.']);
        }

        DB::transaction(function () use ($rows, $audit): void {
            foreach ($rows as ['line' => $line, 'data' => $row]) {
                $data = [
                    'nis' => trim((string) ($row['nis'] ?? '')),
                    'nisn' => trim((string) ($row['nisn'] ?? '')) ?: null,
                    'name' => trim((string) ($row['nama_lengkap'] ?? '')),
                    'gender' => strtoupper(trim((string) ($row['jenis_kelamin'] ?? ''))),
                    'birth_date' => trim((string) ($row['tanggal_lahir'] ?? '')),
                    'birth_place' => trim((string) ($row['tempat_lahir'] ?? '')) ?: null,
                    'religion' => trim((string) ($row['agama'] ?? '')) ?: null,
                    'nik' => trim((string) ($row['nik'] ?? '')) ?: null,
                    'registration_date' => trim((string) ($row['tanggal_terdaftar'] ?? '')) ?: now()->toDateString(),
                    'phone' => trim((string) ($row['no_hp_siswa'] ?? '')) ?: null,
                    'father_name' => trim((string) ($row['nama_ayah'] ?? '')) ?: null,
                    'father_phone' => trim((string) ($row['hp_ayah'] ?? '')) ?: null,
                    'father_occupation' => trim((string) ($row['pekerjaan_ayah'] ?? '')) ?: null,
                    'mother_name' => trim((string) ($row['nama_ibu'] ?? '')) ?: null,
                    'mother_phone' => trim((string) ($row['hp_ibu'] ?? '')) ?: null,
                    'mother_occupation' => trim((string) ($row['pekerjaan_ibu'] ?? '')) ?: null,
                    'guardian_name' => trim((string) ($row['nama_wali'] ?? '')) ?: null,
                    'guardian_phone' => trim((string) ($row['hp_wali'] ?? '')) ?: null,
                    'guardian_occupation' => trim((string) ($row['pekerjaan_wali'] ?? '')) ?: null,
                ];
                $validator = Validator::make($data, [
                    'nis' => ['required', 'string', 'max:30', 'unique:students,nis'],
                    'nisn' => ['nullable', 'digits:10', 'unique:students,nisn'],
                    'name' => ['required', 'string', 'max:255'],
                    'gender' => ['required', Rule::in(['L', 'P'])],
                    'birth_date' => ['required', 'date', 'before_or_equal:today'],
                    'birth_place' => ['nullable', 'string', 'max:100'],
                    'religion' => ['nullable', 'string', 'max:50'],
                    'nik' => ['nullable', 'digits:16', 'unique:students,nik'],
                    'registration_date' => ['required', 'date', 'before_or_equal:today'],
                    'phone' => ['nullable', 'string', 'max:20'],
                    'father_name' => ['nullable', 'string', 'max:255', 'required_without:mother_name', 'required_with:father_phone,father_occupation'],
                    'father_phone' => ['nullable', 'string', 'max:20'],
                    'father_occupation' => ['nullable', 'string', 'max:100'],
                    'mother_name' => ['nullable', 'string', 'max:255', 'required_without:father_name', 'required_with:mother_phone,mother_occupation'],
                    'mother_phone' => ['nullable', 'string', 'max:20'],
                    'mother_occupation' => ['nullable', 'string', 'max:100'],
                    'guardian_name' => ['nullable', 'string', 'max:255', 'required_with:guardian_phone,guardian_occupation'],
                    'guardian_phone' => ['nullable', 'string', 'max:20'],
                    'guardian_occupation' => ['nullable', 'string', 'max:100'],
                ]);
                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'file' => "Baris {$line}: ".implode(' ', $validator->errors()->all()),
                    ]);
                }

                $email = $this->generatedStudentEmail($data['nis'], $data['name']);
                try {
                    $this->ensureGeneratedCredentialsAvailable($email);
                } catch (ValidationException $exception) {
                    $messages = collect($exception->errors())->flatten()->implode(' ');
                    throw ValidationException::withMessages(['file' => "Baris {$line}: {$messages}"]);
                }

                $student = Student::create(collect($data)->except([
                    'father_name', 'father_phone', 'father_occupation', 'mother_name', 'mother_phone',
                    'mother_occupation', 'guardian_name', 'guardian_phone', 'guardian_occupation',
                ])->all() + ['status' => 'aktif']);
                $user = $this->createStudentAccount($student, $email);
                $parents = $this->saveStudentGuardians($student, $data);
                $audit->log('student.imported', $student, null, [
                    ...$student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'birth_place', 'religion', 'nik', 'phone', 'registration_date', 'status']),
                    'account_email' => $user->email,
                    'parents' => $parents,
                ]);
            }
        });

        return back()->with('status', count($rows).' siswa berhasil diimpor. Email dan akun dibuat otomatis.');
    }

    public function updateStudent(Request $request, Student $student, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'digits:10', Rule::unique('students', 'nisn')->ignore($student->id)],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'digits:16', Rule::unique('students', 'nik')->ignore($student->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'registration_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'pindah', 'keluar'])],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'father_name' => ['nullable', 'string', 'max:255', 'required_without:mother_name', 'required_with:father_phone,father_occupation'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'father_occupation' => ['nullable', 'string', 'max:100'],
            'mother_name' => ['nullable', 'string', 'max:255', 'required_without:father_name', 'required_with:mother_phone,mother_occupation'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
            'mother_occupation' => ['nullable', 'string', 'max:100'],
            'guardian_name' => ['nullable', 'string', 'max:255', 'required_with:guardian_phone,guardian_occupation'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'guardian_occupation' => ['nullable', 'string', 'max:100'],
        ]);
        $generatedEmail = $this->generatedStudentEmail($data['nis'], $data['name']);
        $this->ensureGeneratedCredentialsAvailable($generatedEmail, $student->user_id);
        $class = filled($data['class_id'] ?? null) ? SchoolClass::findOrFail($data['class_id']) : null;
        $enrollment = $class ? $student->enrollments()->where('academic_year', $class->academic_year)->first() : null;
        $activeSemester = Semester::active();
        $activeEnrollment = $activeSemester
            ? $student->enrollments()->where('academic_year', $activeSemester->academic_year)->first()
            : null;

        $photoPath = $request->file('photo')?->store('students', 'public');
        $oldPhotoPath = $student->photo_path;
        DB::transaction(function () use ($student, $data, $class, $enrollment, $activeEnrollment, $audit, $photoPath, $generatedEmail): void {
            $old = $student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'birth_place', 'religion', 'nik', 'photo_path', 'phone', 'registration_date', 'status']);
            $old['class_id'] = $enrollment?->class_id ?? $activeEnrollment?->class_id;
            $user = $student->user;
            $old['account'] = $user ? ['username' => $user->username, 'email' => $user->email, 'is_active' => $user->is_active] : null;
            $old['parents'] = $this->studentGuardianSummary($student);
            $student->update(collect($data)->except([
                'class_id', 'photo', 'father_name', 'father_phone', 'father_occupation',
                'mother_name', 'mother_phone', 'mother_occupation', 'guardian_name', 'guardian_phone',
                'guardian_occupation',
            ])->all());
            if ($photoPath) {
                $student->update(['photo_path' => $photoPath]);
            }
            if ($class) {
                if ($enrollment) {
                    $enrollment->update(['class_id' => $class->id]);
                } else {
                    Enrollment::create(['student_id' => $student->id, 'class_id' => $class->id, 'academic_year' => $class->academic_year]);
                }
            }
            $user = $this->synchronizeStudentAccount($student, $generatedEmail);
            $parentSummary = $this->saveStudentGuardians($student, $data);
            $new = $student->only(['nis', 'nisn', 'name', 'gender', 'birth_date', 'birth_place', 'religion', 'nik', 'photo_path', 'phone', 'registration_date', 'status']);
            $new['class_id'] = $class?->id ?? $activeEnrollment?->class_id;
            $new['account'] = $user ? ['username' => $user->username, 'email' => $user->email, 'is_active' => $user->is_active] : null;
            $new['parents'] = $parentSummary;
            $audit->log('student.updated', $student, $old, $new);
        });

        if ($photoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()->route('master-data.section', ['section' => 'siswa', 'tab' => 'data'])
            ->with('status', "Data siswa {$student->name} berhasil diperbarui.");
    }

    public function destroyStudent(Student $student, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($student, $audit): void {
            $old = $student->only(['nis', 'nisn', 'name', 'gender', 'registration_date', 'status']);
            $student->update(['status' => 'keluar']);
            if ($student->user) {
                $student->user->update(['is_active' => false]);
            }
            $student->delete();
            $audit->log('student.archived', $student, $old, null);
        });

        return redirect()->route('master-data.section', ['section' => 'siswa', 'tab' => 'data'])
            ->with('status', "Data siswa {$student->name} diarsipkan; riwayat akademiknya tetap tersimpan.");
    }

    private function generatedStudentEmail(string $nis, string $name): string
    {
        $safeNis = preg_replace('/[^a-z0-9.-]/i', '', $nis) ?: 'siswa';
        $separator = config('student_accounts.email_separator', '.');
        $namePart = Str::slug($name, $separator) ?: 'siswa';
        $namePart = substr($namePart, 0, max(1, 64 - strlen($safeNis) - strlen($separator)));
        $localPart = strtolower($safeNis.$separator.$namePart);
        $domain = strtolower(trim(config('student_accounts.email_domain', 'gmail.com')));

        return $localPart.'@'.$domain;
    }

    private function ensureGeneratedCredentialsAvailable(string $email, ?int $ignoreUserId = null): void
    {
        $collision = User::withTrashed()->where(function ($query) use ($email) {
            $query->where('email', $email)->orWhere('username', $email);
        })->when($ignoreUserId, fn ($query) => $query->where('id', '<>', $ignoreUserId))->exists();

        if ($collision) {
            throw ValidationException::withMessages([
                'nis' => 'Email otomatis siswa sudah dipakai akun lain. Ubah NIS atau nama siswa terlebih dahulu.',
            ]);
        }
    }

    private function studentBirthDatePassword(Student $student): string
    {
        return \Illuminate\Support\Carbon::parse($student->birth_date)
            ->format(config('student_accounts.password_date_format', 'Y-m-d'));
    }

    private function createStudentAccount(Student $student, string $email): User
    {
        $user = User::create([
            'name' => $student->name,
            'username' => $email,
            'email' => $email,
            'password' => $this->studentBirthDatePassword($student),
            'role' => 'siswa',
            'is_active' => $student->status === 'aktif',
        ]);
        $user->assignRole('siswa');
        $student->update(['user_id' => $user->id]);

        return $user;
    }

    private function synchronizeStudentAccount(Student $student, string $email): User
    {
        $user = $student->user;
        if (! $user) {
            return $this->createStudentAccount($student, $email);
        }

        $user->update([
            'name' => $student->name,
            'username' => $email,
            'email' => $email,
            'password' => $this->studentBirthDatePassword($student),
            'is_active' => $student->status === 'aktif',
        ]);
        if (! $user->hasRole('siswa')) {
            $user->assignRole('siswa');
        }

        return $user;
    }

    private function saveStudentGuardians(Student $student, array $data): array
    {
        $relationships = [
            'father' => ['ayah', 'father_name', 'father_phone', 'father_occupation'],
            'mother' => ['ibu', 'mother_name', 'mother_phone', 'mother_occupation'],
            'guardian' => ['wali', 'guardian_name', 'guardian_phone', 'guardian_occupation'],
        ];

        foreach ($relationships as [$relationship, $nameField, $phoneField, $occupationField]) {
            $guardian = $student->guardians()->wherePivot('relationship', $relationship)->with('user')->first();
            $name = trim($data[$nameField] ?? '');

            if ($name === '') {
                if ($guardian) {
                    $student->guardians()->detach($guardian->id);
                }

                continue;
            }

            $user = $guardian?->user;
            if (! $user) {
                $user = User::create([
                    'name' => $name,
                    'username' => 'wali-'.$student->id.'-'.$relationship.'-'.Str::lower(Str::random(8)),
                    'email' => null,
                    'password' => Str::random(48),
                    'role' => 'orangtua',
                    'is_active' => true,
                ]);
                $user->assignRole('orangtua');
            } else {
                $user->update(['name' => $name, 'phone' => $data[$phoneField] ?? null]);
                if (! $user->hasRole('orangtua')) {
                    $user->assignRole('orangtua');
                }
            }

            $guardian ??= new Guardian;
            $guardian->fill([
                'user_id' => $user->id,
                'name' => $name,
                'phone' => $data[$phoneField] ?? null,
                'occupation' => $data[$occupationField] ?? null,
            ]);
            $guardian->save();
            $student->guardians()->syncWithoutDetaching([
                $guardian->id => ['relationship' => $relationship],
            ]);
        }

        return $this->studentGuardianSummary($student);
    }

    private function studentGuardianSummary(Student $student): array
    {
        return $student->guardians()->with('user')->get()->map(fn (Guardian $guardian) => [
            'name' => $guardian->name,
            'phone' => $guardian->phone,
            'occupation' => $guardian->occupation,
            'relationship' => $guardian->pivot?->relationship,
            'username' => $guardian->user?->username,
        ])->all();
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

    public function editSubject(Subject $subject): View
    {
        return view('master-data.record-edit', ['type' => 'mapel', 'record' => $subject]);
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

    public function editTeacher(Teacher $teacher): View
    {
        return view('master-data.record-edit', ['type' => 'guru', 'record' => $teacher->load('user')]);
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

        return redirect()->route('master-data.section', ['section' => 'penugasan', 'tab' => 'data'])
            ->with('status', 'Penugasan guru berhasil dihapus. Riwayat nilai dan absensi tetap tersimpan.');
    }

    public function editAssignment(TeachingAssignment $assignment): View
    {
        return view('master-data.record-edit', ['type' => 'penugasan', 'record' => $assignment->load(['teacher', 'schoolClass', 'subject', 'semester'])]);
    }
}
