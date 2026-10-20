<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;

/**
 * Skor kehadiran 0-100 = jumlah (kontribusi tiap status) / total catatan x 100.
 * Kontribusi per status diatur di settings `kpi.attendance_impact`.
 */
class AttendanceCalculator
{
    public function __construct(private SettingService $settings)
    {
    }

    public function forStudent(Student $student, Semester $semester): array
    {
        $statuses = ['hadir', 'sakit', 'izin', 'alfa', 'bolos'];

        $counts = StudentAttendance::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $counts = array_merge(array_fill_keys($statuses, 0), array_map('intval', $counts));
        $total = array_sum($counts);

        $impact = $this->settings->get('kpi.attendance_impact', [
            'hadir' => 1, 'sakit' => 0.9, 'izin' => 0.9, 'alfa' => 0, 'bolos' => 0,
        ]);

        $score = null;
        if ($total > 0) {
            $points = 0.0;
            foreach ($counts as $status => $n) {
                $points += $n * (float) ($impact[$status] ?? 0);
            }
            $score = round($points / $total * 100, 2);
        }

        return ['score' => $score, 'counts' => $counts, 'total' => $total];
    }
}