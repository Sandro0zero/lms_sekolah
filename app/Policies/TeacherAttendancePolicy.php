<?php

namespace App\Policies;

use App\Models\TeacherAttendance;
use App\Models\User;

/** Guru tidak boleh mengubah absensinya sendiri. Koreksi hanya oleh admin (wajib alasan). */
class TeacherAttendancePolicy
{
    public function update(User $user, TeacherAttendance $attendance): bool
    {
        return $user->hasRole('admin');
    }

    public function requiresReason(User $user, TeacherAttendance $attendance): bool
    {
        return $user->hasRole('admin');
    }
}