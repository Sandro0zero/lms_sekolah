<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassRosterController extends Controller
{
    public function index(Request $request): View
    {
        $semester = Semester::active();
        if (! $semester) {
            return response()->view('shared.no-semester');
        }

        $teachers = Teacher::query()
            ->where('is_active', true)
            ->with(['homeroomClasses' => fn ($query) => $query->where('academic_year', $semester->academic_year)->orderBy('name')])
            ->orderBy('name')->get();

        $selectedTeacherId = $request->integer('teacher_id') ?: null;
        $selectedTeacher = $teachers->firstWhere('id', $selectedTeacherId);
        $classes = $selectedTeacher?->homeroomClasses ?? collect();
        $selectedClassId = $request->integer('class_id') ?: null;
        $selectedClass = $classes->firstWhere('id', $selectedClassId);
        $search = trim($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? '');

        if (! $selectedClass && $classes->count() === 1) {
            $selectedClass = $classes->first();
            $selectedClassId = $selectedClass->id;
        }

        $students = $selectedClass
            ? Student::query()->where('status', 'aktif')
                ->with(['enrollments' => fn ($query) => $query->where('academic_year', $semester->academic_year)->with('schoolClass')])
                ->when($search !== '', fn ($query) => $query->where(fn ($studentQuery) => $studentQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")))
                ->orderByDesc('registration_date')->orderBy('name')->get()
            : collect();

        return view('class-roster.index', compact(
            'semester', 'teachers', 'selectedTeacher', 'selectedTeacherId', 'classes',
            'selectedClass', 'selectedClassId', 'students', 'search'
        ));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu.');

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', Rule::exists('teachers', 'id')->where('is_active', true)],
        ]);
        $data += $request->validate([
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')->where(fn ($query) => $query
                ->where('academic_year', $semester->academic_year)
                ->where('homeroom_teacher_id', $data['teacher_id']))],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'distinct', Rule::exists('students', 'id')
                ->where('status', 'aktif')->whereNull('deleted_at')],
        ]);
        $data += $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $class = SchoolClass::findOrFail($data['class_id']);
        $moved = 0;
        $unchanged = 0;

        DB::transaction(function () use ($data, $class, $semester, $audit, &$moved, &$unchanged): void {
            foreach ($data['student_ids'] as $studentId) {
                $enrollment = Enrollment::firstOrNew([
                    'student_id' => $studentId,
                    'academic_year' => $semester->academic_year,
                ]);
                if ($enrollment->exists && (int) $enrollment->class_id === (int) $class->id) {
                    $unchanged++;
                    continue;
                }

                $old = $enrollment->exists ? $enrollment->only(['class_id', 'academic_year']) : null;
                $enrollment->class_id = $class->id;
                $enrollment->academic_year = $semester->academic_year;
                $enrollment->save();
                $audit->log($old ? 'student.class_changed' : 'student.enrolled', $enrollment, $old,
                    ['student_id' => (int) $studentId, 'class_id' => $class->id, 'academic_year' => $semester->academic_year]);
                $moved++;
            }
        });

        $query = [
            'teacher_id' => $data['teacher_id'], 'class_id' => $class->id,
        ];
        if (filled($data['q'] ?? null)) {
            $query['q'] = $data['q'];
        }

        return redirect()->route('class-roster.index', $query)
            ->with('status', "{$moved} siswa ditambahkan/dipindahkan ke kelas {$class->name}; {$unchanged} siswa sudah berada di kelas tersebut.");
    }
}
