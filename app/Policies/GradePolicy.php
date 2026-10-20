<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\EditWindow;

/**
 * Admin: boleh kapan saja (wajib alasan jika melewati batas hari).
 * Guru : hanya untuk kelas+mapel yang ia ajar, dan hanya dalam batas hari sejak input pertama.
 * Lainnya: tidak boleh mengubah nilai.
 */
class GradePolicy
{
    private const WINDOW_KEY = 'grade.edit_window_days';

    public function __construct(private EditWindow $window)
    {
    }

    public function update(User $user, Grade $grade): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('guru') || ! $user->teacher) {
            return false;
        }

        $teaches = TeachingAssignment::where('teacher_id', $user->teacher->id)
            ->where('class_id', $grade->class_id)
            ->where('subject_id', $grade->subject_id)
            ->where('semester_id', $grade->semester_id)
            ->exists();

        return $teaches && $this->window->isOpen($grade->created_at, self::WINDOW_KEY);
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }

    /** True jika perubahan ini harus disertai alasan (admin mengubah setelah batas hari). */
    public function requiresReason(User $user, Grade $grade): bool
    {
        return $user->hasRole('admin') && ! $this->window->isOpen($grade->created_at, self::WINDOW_KEY);
    }
}