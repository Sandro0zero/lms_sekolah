<?php

namespace App\Policies;

use App\Models\StudentAttendance;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\EditWindow;

/** Aturan sama dengan nilai, dengan batas hari dari settings `attendance.edit_window_days`. */
class StudentAttendancePolicy
{
    private const WINDOW_KEY = 'attendance.edit_window_days';

    public function __construct(private EditWindow $window)
    {
    }

    public function update(User $user, StudentAttendance $attendance): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('guru') || ! $user->teacher) {
            return false;
        }

        $teaches = TeachingAssignment::where('teacher_id', $user->teacher->id)
            ->where('class_id', $attendance->class_id)
            ->where('subject_id', $attendance->subject_id)
            ->where('semester_id', $attendance->semester_id)
            ->exists();

        return $teaches && $this->window->isOpen($attendance->created_at, self::WINDOW_KEY);
    }

    public function requiresReason(User $user, StudentAttendance $attendance): bool
    {
        return $user->hasRole('admin') && ! $this->window->isOpen($attendance->created_at, self::WINDOW_KEY);
    }
}