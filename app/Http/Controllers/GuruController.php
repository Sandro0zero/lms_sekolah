<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\TeachingAssignment;

class GuruController extends Controller
{
    public function home()
    {
        $user = $this->user();
        abort_unless($user->hasRole('guru') && $user->teacher, 403);

        $teacher = $user->teacher;
        $semester = Semester::active();
        if (! $semester) {
            return response()->view('shared.no-semester');
        }

        $assignments = TeachingAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('semester_id', $semester->id)
            ->get()
            ->sortBy('schoolClass.name');

        $homeroomClasses = $teacher->homeroomClasses()
            ->where('academic_year', $semester->academic_year)
            ->with('students')
            ->get();

        return view('guru.home', compact('teacher', 'semester', 'assignments', 'homeroomClasses'));
    }
}
