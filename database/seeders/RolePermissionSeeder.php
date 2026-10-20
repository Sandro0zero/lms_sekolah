<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'users.manage',
            'master.view', 'master.manage',
            'settings.view', 'settings.manage',
            'student-attendance.view', 'student-attendance.manage',
            'teacher-attendance.view', 'teacher-attendance.manage', 'teacher-attendance.approve',
            'grades.view', 'grades.manage', 'grades.update-after-window',
            'behavior.view', 'behavior.manage',
            'reports.view',
            'kpi.view', 'kpi.calculate', 'kpi.publish',
            'audit.view',
        ];

        $permissionModels = collect($permissions)
            ->mapWithKeys(fn (string $name) => [$name => Permission::findOrCreate($name, 'web')]);

        $rolePermissions = [
            'admin' => $permissions,
            'kepsek' => [
                'dashboard.view', 'master.view', 'settings.view',
                'student-attendance.view', 'teacher-attendance.view', 'teacher-attendance.approve',
                'grades.view', 'behavior.view', 'reports.view',
                'kpi.view', 'kpi.calculate', 'kpi.publish', 'audit.view',
            ],
            'guru' => [
                'dashboard.view', 'student-attendance.view', 'student-attendance.manage',
                'teacher-attendance.view', 'teacher-attendance.manage',
                'grades.view', 'grades.manage', 'behavior.view', 'behavior.manage', 'reports.view',
            ],
            'siswa' => ['dashboard.view', 'student-attendance.view', 'reports.view'],
            'orangtua' => ['dashboard.view', 'student-attendance.view', 'reports.view'],
        ];

        foreach ($rolePermissions as $name => $allowed) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($permissionModels->only($allowed)->values());
        }

        // Migrasikan otorisasi akun lama dari kolom role yang telah ada.
        User::query()->whereNotNull('role')->chunkById(200, function ($users): void {
            foreach ($users as $user) {
                if (Role::where('name', $user->role)->where('guard_name', 'web')->exists()) {
                    $user->syncRoles([$user->role]);
                }
            }
        });
    }
}
