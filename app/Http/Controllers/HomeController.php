<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentKpi;
use App\Models\Teacher;

class HomeController extends Controller
{
    public function __invoke()
    {
        $user = $this->user();

        if ($user->hasRole('admin')) {
            $semester = Semester::active();
            $year = $semester?->academic_year;

            $classes = $semester
                ? SchoolClass::query()->where('academic_year', $year)
                    ->with('homeroomTeacher')
                    ->withCount([
                        'enrollments as active_students_count' => fn ($query) => $query->where('academic_year', $year)
                            ->whereHas('student', fn ($student) => $student->where('status', 'aktif')),
                        'assignments as active_assignments_count' => fn ($query) => $query->where('semester_id', $semester->id),
                    ])->orderBy('name')->limit(8)->get()
                : collect();

            return view('admin.dashboard', [
                'semester' => $semester,
                'stats' => [
                    'students' => Student::where('status', 'aktif')->count(),
                    'teachers' => Teacher::where('is_active', true)->count(),
                    'guardians' => Guardian::whereHas('user', fn ($query) => $query->where('is_active', true))->count(),
                    'classes' => $semester ? SchoolClass::where('academic_year', $year)->count() : SchoolClass::count(),
                    'unplaced_students' => $semester ? Student::where('status', 'aktif')->whereDoesntHave('enrollments', fn ($query) => $query->where('academic_year', $year))->count() : null,
                    'classes_without_homeroom' => $semester ? SchoolClass::where('academic_year', $year)->whereNull('homeroom_teacher_id')->count() : null,
                    'classes_without_assignments' => $semester ? SchoolClass::where('academic_year', $year)
                        ->whereDoesntHave('assignments', fn ($query) => $query->where('semester_id', $semester->id))->count() : null,
                    'kpi_incomplete' => $semester ? Student::where('status', 'aktif')
                        ->whereHas('enrollments', fn ($query) => $query->where('academic_year', $year))
                        ->whereDoesntHave('kpis', fn ($query) => $query->where('semester_id', $semester->id)->whereNotNull('final_score'))
                        ->count() : null,
                    'kpi_published' => $semester ? StudentKpi::where('semester_id', $semester->id)->where('status', 'published')->count() : null,
                ],
                'classesList' => $classes,
            ]);
        }

        return match (true) {
            $user->hasRole('kepsek') => redirect()->route('kpi.index'),
            $user->hasRole('guru') => redirect()->route('guru.home'),
            $user->hasRole('orangtua') => redirect()->route('guardian.index'),
            $user->hasRole('siswa') => redirect()->route('student.home'),
            default => abort(403),
        };
    }
}
