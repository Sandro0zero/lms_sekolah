<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Nilai awal pengaturan. Semuanya bisa diubah admin lewat halaman Pengaturan.
 */
class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $settings = [
            ['grade.edit_window_days', 7, 'Batas hari guru boleh mengubah nilai sejak input pertama'],
            ['attendance.edit_window_days', 7, 'Batas hari guru boleh mengubah absensi siswa sejak input pertama'],
            ['grade.default_weights', ['uh' => 40, 'uts' => 30, 'uas' => 30], 'Bobot nilai standar sekolah (total 100)'],
            ['grade.default_kkm', 75, 'KKM standar jika mapel belum diatur khusus'],
            ['grade.remedial_max_score', 'kkm', 'Nilai maksimal hasil remedial: "kkm" atau angka'],
            ['kpi.weights', ['grade' => 50, 'attendance' => 30, 'behavior' => 20], 'Bobot komponen KPI siswa (total 100)'],
            ['kpi.attendance_impact', ['hadir' => 1, 'sakit' => 0.9, 'izin' => 0.9, 'alfa' => 0, 'bolos' => 0], 'Nilai kontribusi tiap status absensi terhadap skor kehadiran (0-1)'],
            ['kpi.show_rank_to_parents', false, 'Tampilkan peringkat kelas ke orang tua'],
            ['kpi.student_can_view', true, 'Siswa boleh melihat KPI miliknya'],
            ['behavior.scale_max', 4, 'Skor tertinggi penilaian perilaku (skala 1 sampai angka ini)'],
            ['behavior.homeroom_weight', 1, 'Pengali bobot penilaian wali kelas dibanding guru lain'],
            ['attendance.student_self_checkin_enabled', false, 'Absen mandiri siswa (ditunda ke tahap berikutnya)'],
        ];

        foreach ($settings as [$key, $value, $description]) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value), 'description' => $description, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $categories = [
            ['Sangat Baik', 90, 100, 1],
            ['Baik', 80, 89.99, 2],
            ['Cukup', 70, 79.99, 3],
            ['Perlu Pembinaan', 0, 69.99, 4],
        ];

        foreach ($categories as [$name, $min, $max, $order]) {
            DB::table('kpi_categories')->updateOrInsert(
                ['name' => $name],
                ['min_score' => $min, 'max_score' => $max, 'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $aspects = ['Kedisiplinan', 'Sopan santun', 'Tanggung jawab', 'Kerja sama', 'Kejujuran'];

        foreach ($aspects as $i => $name) {
            DB::table('behavior_aspects')->updateOrInsert(
                ['name' => $name],
                ['sort_order' => $i + 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}