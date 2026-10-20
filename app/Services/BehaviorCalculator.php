<?php

namespace App\Services;

use App\Models\BehaviorAssessment;
use App\Models\Semester;
use App\Models\Student;

/**
 * Skor perilaku 0-100 = rata-rata skor semua aspek dari semua guru yang sudah
 * mengirim penilaian, dibagi skala maksimal, dikali 100.
 * Penilaian wali kelas dikali `behavior.homeroom_weight`.
 */
class BehaviorCalculator
{
    public function __construct(private SettingService $settings)
    {
    }

    public function forStudent(Student $student, Semester $semester): array
    {
        $class = $student->classForYear($semester->academic_year);
        $homeroomId = $class?->homeroom_teacher_id;

        $max = (int) $this->settings->get('behavior.scale_max', 4);
        $homeroomWeight = (float) $this->settings->get('behavior.homeroom_weight', 1);

        $assessments = BehaviorAssessment::with('scores')
            ->where('semester_id', $semester->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->get();

        $sum = 0.0;
        $weightSum = 0.0;

        foreach ($assessments as $assessment) {
            if ($assessment->scores->isEmpty()) {
                continue;
            }
            $weight = $assessment->teacher_id === $homeroomId ? $homeroomWeight : 1.0;
            $sum += $assessment->scores->avg('score') * $weight;
            $weightSum += $weight;
        }

        $score = $weightSum > 0 ? round(($sum / $weightSum) / $max * 100, 2) : null;

        return ['score' => $score, 'assessors' => $assessments->count()];
    }
}