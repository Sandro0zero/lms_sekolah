<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Policies\GradePolicy;
use App\Policies\StudentAttendancePolicy;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeachingRecordController extends Controller
{
    public function grades(Request $request): View
    {
        $semester = Semester::active();
        $assignments = $semester ? $this->assignments($this->user(), $semester) : collect();
        [$class, $subject] = $this->selectedPair($request, $semester, $assignments);
        $type = in_array($request->query('type'), ['uh', 'uts', 'uas'], true) ? $request->query('type') : 'uh';
        $sequence = $type === 'uh' ? max(1, min(99, $request->integer('sequence', 1))) : 1;
        $defaultDate = now()->toDateString();
        if ($semester && $defaultDate < $semester->start_date->toDateString()) {
            $defaultDate = $semester->start_date->toDateString();
        } elseif ($semester && $defaultDate > $semester->end_date->toDateString()) {
            $defaultDate = $semester->end_date->toDateString();
        }
        $students = $class && $subject && $semester
            ? $this->roster($class, $semester)->map(function (Student $student) use ($subject, $semester, $type, $sequence) {
                $student->current_grade = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)->where('semester_id', $semester->id)
                    ->where('type', $type)->where('sequence', $sequence)->first();

                return $student;
            })
            : collect();

        return view('records.grades', [
            'semester' => $semester,
            'assignments' => $assignments,
            'selectedClass' => $class,
            'selectedSubject' => $subject,
            'type' => $type,
            'sequence' => $sequence,
            'defaultDate' => $defaultDate,
            'students' => $students,
            'canEdit' => $this->user()->can('grades.manage'),
        ]);
    }

    public function saveGrades(Request $request, GradePolicy $policy, AuditLogger $audit): RedirectResponse
    {
        abort_unless($this->user()->can('grades.manage'), 403);
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu.');

        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'type' => ['required', 'in:uh,uts,uas'],
            'sequence' => ['required', 'integer', 'between:1,99'],
            'title' => ['nullable', 'string', 'max:255'],
            'assessed_on' => ['required', 'date', 'after_or_equal:'.$semester->start_date->toDateString(), 'before_or_equal:'.$semester->end_date->toDateString()],
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'between:0,100'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['type'] !== 'uh' && (int) $data['sequence'] !== 1) {
            return back()->withErrors(['sequence' => 'UTS dan UAS hanya memiliki satu nilai.'])->withInput();
        }

        $user = $this->user();
        $class = SchoolClass::findOrFail($data['class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $this->assertAssignment($user, $semester, $class, $subject);
        $rosterIds = $this->roster($class, $semester)->modelKeys();
        abort_unless(array_diff(array_map('intval', array_keys($data['scores'])), $rosterIds) === [], 422, 'Daftar siswa tidak cocok dengan kelas yang dipilih.');

        DB::transaction(function () use ($data, $semester, $class, $subject, $user, $policy, $audit, $rosterIds): void {
            foreach ($data['scores'] as $studentId => $score) {
                if ($score === null || $score === '') {
                    continue;
                }

                abort_unless(in_array((int) $studentId, $rosterIds, true), 422, 'Siswa bukan anggota kelas ini.');
                $grade = Grade::firstOrNew([
                    'student_id' => (int) $studentId,
                    'subject_id' => $subject->id,
                    'semester_id' => $semester->id,
                    'type' => $data['type'],
                    'sequence' => (int) $data['sequence'],
                ]);
                $old = $grade->exists ? $grade->only(['score', 'title', 'assessed_on']) : null;

                if ($grade->exists) {
                    abort_unless($policy->update($user, $grade), 403, 'Masa perubahan nilai untuk data ini sudah berakhir.');
                    if ($policy->requiresReason($user, $grade) && trim((string) ($data['reason'] ?? '')) === '') {
                        abort(422, 'Alasan wajib diisi untuk mengubah nilai yang sudah melewati batas waktu.');
                    }
                }

                $grade->fill([
                    'class_id' => $class->id,
                    'title' => $data['title'] ?? null,
                    'score' => $score,
                    'assessed_on' => $data['assessed_on'],
                    'input_by' => $grade->exists ? $grade->input_by : $user->id,
                    'updated_by' => $grade->exists ? $user->id : null,
                ]);
                $grade->save();
                $audit->log($old ? 'grade.updated' : 'grade.created', $grade, $old,
                    $grade->only(['score', 'title', 'assessed_on']), $old ? ($data['reason'] ?? null) : null);
            }
        });

        return redirect()->route('grades.index', [
            'class' => $class->id, 'subject' => $subject->id, 'type' => $data['type'], 'sequence' => $data['sequence'],
        ])->with('status', 'Nilai berhasil disimpan.');
    }

    public function attendance(Request $request): View
    {
        $semester = Semester::active();
        $assignments = $semester ? $this->assignments($this->user(), $semester) : collect();
        [$class, $subject] = $this->selectedPair($request, $semester, $assignments);
        $date = $request->query('date', now()->toDateString());
        $period = max(1, min(20, $request->integer('period', 1)));
        $students = $class && $subject && $semester
            ? $this->roster($class, $semester)->map(function (Student $student) use ($subject, $date, $period) {
                $student->current_attendance = StudentAttendance::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)->where('date', $date)->where('lesson_period', $period)->first();

                return $student;
            })
            : collect();

        return view('records.attendance', [
            'semester' => $semester,
            'assignments' => $assignments,
            'selectedClass' => $class,
            'selectedSubject' => $subject,
            'date' => $date,
            'dateInSemester' => $semester && $date >= $semester->start_date->toDateString() && $date <= $semester->end_date->toDateString(),
            'period' => $period,
            'students' => $students,
            'canEdit' => $this->user()->can('student-attendance.manage'),
        ]);
    }

    public function saveAttendance(Request $request, StudentAttendancePolicy $policy, AuditLogger $audit): RedirectResponse
    {
        abort_unless($this->user()->can('student-attendance.manage'), 403);
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu.');

        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'date' => ['required', 'date', 'after_or_equal:'.$semester->start_date->toDateString(), 'before_or_equal:'.$semester->end_date->toDateString(), 'before_or_equal:today'],
            'period' => ['required', 'integer', 'between:1,20'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['nullable', 'in:hadir,sakit,izin,alfa,bolos'],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $this->user();
        $class = SchoolClass::findOrFail($data['class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $this->assertAssignment($user, $semester, $class, $subject);
        $rosterIds = $this->roster($class, $semester)->modelKeys();
        abort_unless(array_diff(array_map('intval', array_keys($data['statuses'])), $rosterIds) === [], 422, 'Daftar siswa tidak cocok dengan kelas yang dipilih.');

        DB::transaction(function () use ($data, $semester, $class, $subject, $user, $policy, $audit, $rosterIds): void {
            foreach ($data['statuses'] as $studentId => $status) {
                if ($status === null || $status === '') {
                    continue;
                }

                abort_unless(in_array((int) $studentId, $rosterIds, true), 422, 'Siswa bukan anggota kelas ini.');
                $note = trim((string) ($data['notes'][$studentId] ?? ''));
                if ($status !== 'hadir' && $note === '') {
                    abort(422, 'Keterangan wajib diisi untuk status selain hadir.');
                }

                $attendance = StudentAttendance::firstOrNew([
                    'student_id' => (int) $studentId,
                    'subject_id' => $subject->id,
                    'date' => $data['date'],
                    'lesson_period' => (int) $data['period'],
                ]);
                $old = $attendance->exists ? $attendance->only(['status', 'note']) : null;

                if ($attendance->exists) {
                    abort_unless($policy->update($user, $attendance), 403, 'Masa perubahan absensi untuk data ini sudah berakhir.');
                    if ($policy->requiresReason($user, $attendance) && trim((string) ($data['reason'] ?? '')) === '') {
                        abort(422, 'Alasan wajib diisi untuk mengubah absensi yang sudah melewati batas waktu.');
                    }
                }

                $attendance->fill([
                    'class_id' => $class->id,
                    'semester_id' => $semester->id,
                    'status' => $status,
                    'note' => $note !== '' ? $note : null,
                    'source' => $user->hasRole('admin') ? 'admin' : 'guru',
                    'recorded_by' => $attendance->exists ? $attendance->recorded_by : $user->id,
                    'updated_by' => $attendance->exists ? $user->id : null,
                ]);
                $attendance->save();
                $audit->log($old ? 'student_attendance.updated' : 'student_attendance.created', $attendance, $old,
                    $attendance->only(['status', 'note', 'date', 'lesson_period']), $old ? ($data['reason'] ?? null) : null);
            }
        });

        return redirect()->route('attendance.index', [
            'class' => $class->id, 'subject' => $subject->id, 'date' => $data['date'], 'period' => $data['period'],
        ])->with('status', 'Absensi berhasil disimpan.');
    }

    private function assignments(User $user, Semester $semester)
    {
        $query = TeachingAssignment::with(['schoolClass', 'subject', 'teacher'])
            ->where('semester_id', $semester->id);

        if ($user->hasRole('guru')) {
            abort_unless($user->teacher, 403);
            $query->where('teacher_id', $user->teacher->id);
        } elseif (! $user->hasAnyRole('admin', 'kepsek')) {
            abort(403);
        }

        return $query->orderBy('class_id')->get();
    }

    private function selectedPair(Request $request, ?Semester $semester, $assignments): array
    {
        if (! $semester || ! $request->filled('class') || ! $request->filled('subject')) {
            return [null, null];
        }

        $assignment = $assignments->first(fn (TeachingAssignment $item) =>
            $item->class_id === $request->integer('class')
            && $item->subject_id === $request->integer('subject'));

        abort_unless($assignment, 403, 'Kelas atau mata pelajaran itu tidak termasuk penugasan yang boleh Anda lihat.');

        return [$assignment->schoolClass, $assignment->subject];
    }

    private function assertAssignment(User $user, Semester $semester, SchoolClass $class, Subject $subject): void
    {
        $query = TeachingAssignment::where('semester_id', $semester->id)
            ->where('class_id', $class->id)->where('subject_id', $subject->id);

        if ($user->hasRole('guru')) {
            abort_unless($user->teacher, 403);
            $query->where('teacher_id', $user->teacher->id);
        } elseif (! $user->hasAnyRole('admin', 'kepsek')) {
            abort(403);
        }

        abort_unless($query->exists(), 403, 'Belum ada penugasan guru untuk kelas dan mata pelajaran ini.');
        abort_unless($class->academic_year === $semester->academic_year, 422, 'Kelas tidak sesuai dengan tahun ajaran semester aktif.');
    }

    private function roster(SchoolClass $class, Semester $semester)
    {
        return Student::query()->where('status', 'aktif')
            ->whereHas('enrollments', fn ($query) => $query
                ->where('class_id', $class->id)
                ->where('academic_year', $semester->academic_year))
            ->orderBy('name')->get();
    }
}
