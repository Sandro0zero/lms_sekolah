<?php

namespace App\Policies;

use App\Models\Semester;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\User;

/** Siapa yang boleh melihat laporan seorang siswa. */
class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole('admin', 'kepsek')) {
            return true;
        }

        if ($user->hasRole('orangtua')) {
            return $user->guardian
                ? $user->guardian->students()->where('students.id', $student->id)->exists()
                : false;
        }

        if ($user->hasRole('siswa')) {
            return $user->student?->id === $student->id;
        }

        if ($user->hasRole('guru') && $user->teacher) {
            $semester = Semester::active();
            $class = $semester ? $student->classForYear($semester->academic_year) : null;
            if (! $class) {
                return false;
            }

            return $class->homeroom_teacher_id === $user->teacher->id
                || TeachingAssignment::where('teacher_id', $user->teacher->id)
                    ->where('class_id', $class->id)
                    ->where('semester_id', $semester->id)
                    ->exists();
        }

        return false;
    }
}
