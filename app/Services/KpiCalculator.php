<?php

namespace App\Services;

use App\Models\KpiCategory;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentKpi;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * KPI siswa per semester = nilai + kehadiran + perilaku (bobot dari settings `kpi.weights`).
 * Skor akhir hanya dihitung jika ketiga komponen lengkap; jika tidak, final_score = null.
 * KPI yang sudah dipublikasikan tidak dihitung ulang (snapshot).
 */
class KpiCalculator
{
    public function __construct(
        private SettingService $settings,
        private GradeCalculator $grades,
        private AttendanceCalculator $attendance,
        private BehaviorCalculator $behavior,
        private AuditLogger $audit,
    ) {
    }

    public function calculate(Student $student, Semester $semester): ?StudentKpi
    {
        $existing = StudentKpi::where('student_id', $student->id)
            ->where('semester_id', $semester->id)->first();

        if ($existing && $existing->isPublished()) {
            return $existing;
        }

        $class = $student->classForYear($semester->academic_year);
        if (! $class) {
            return null;
        }

        // 1) Nilai: hanya dihitung jika SEMUA mapel sudah lengkap (UH, UTS, UAS)
        $subjects = $this->grades->forStudent($student, $semester);
        $gradesReady = $subjects->isNotEmpty() && $subjects->every(fn ($s) => $s['complete']);
        $gradeScore = $gradesReady ? round($subjects->pluck('final')->avg(), 2) : null;

        // 2) Kehadiran dan 3) Perilaku
        $att = $this->attendance->forStudent($student, $semester);
        $beh = $this->behavior->forStudent($student, $semester);

        $w = $this->settings->get('kpi.weights', ['grade' => 50, 'attendance' => 30, 'behavior' => 20]);

        $missing = [];
        if ($gradeScore === null) {
            $missing[] = 'nilai';
        }
        if ($att['score'] === null) {
            $missing[] = 'kehadiran';
        }
        if ($beh['score'] === null) {
            $missing[] = 'perilaku';
        }

        $weightTotal = array_sum($w);
        $final = null;
        if (! $missing && $weightTotal > 0) {
            $final = round(
                ($gradeScore * $w['grade'] + $att['score'] * $w['attendance'] + $beh['score'] * $w['behavior'])
                / $weightTotal,
                2
            );
        }

        $oldValues = $existing?->only([
            'class_id', 'grade_score', 'attendance_score', 'behavior_score',
            'weight_grade', 'weight_attendance', 'weight_behavior', 'final_score',
            'category', 'previous_final_score', 'breakdown', 'status',
        ]);

        $kpi = StudentKpi::updateOrCreate(
            ['student_id' => $student->id, 'semester_id' => $semester->id],
            [
                'class_id' => $class->id,
                'grade_score' => $gradeScore,
                'attendance_score' => $att['score'],
                'behavior_score' => $beh['score'],
                'weight_grade' => $w['grade'],
                'weight_attendance' => $w['attendance'],
                'weight_behavior' => $w['behavior'],
                'final_score' => $final,
                'category' => $final !== null ? $this->categoryFor($final) : null,
                'previous_final_score' => $this->previousScore($student, $semester),
                'breakdown' => [
                    'subjects' => $subjects->values()->all(),
                    'attendance' => $att['counts'],
                    'behavior_assessors' => $beh['assessors'],
                    'missing' => $missing,
                ],
                'status' => 'draft', // dihitung ulang berarti harus diusulkan/dipublikasikan lagi
                'ready_by' => null,
                'ready_at' => null,
                'calculated_at' => now(),
            ]
        );

        $this->audit->log(
            'calculated',
            $kpi,
            $oldValues,
            $kpi->only([
                'class_id', 'grade_score', 'attendance_score', 'behavior_score',
                'weight_grade', 'weight_attendance', 'weight_behavior', 'final_score',
                'category', 'previous_final_score', 'breakdown', 'status',
            ]),
        );

        return $kpi;
    }

    /**
     * Publikasikan KPI (hanya admin/kepsek). Hanya yang skor akhirnya lengkap.
     * Mengembalikan jumlah yang dipublikasikan dan yang dilewati.
     */
    public function publish(Semester $semester, User $by, ?SchoolClass $class = null): array
    {
        if (! $by->hasAnyRole('admin', 'kepsek')) {
            throw new AuthorizationException('Hanya admin atau kepala sekolah yang dapat mempublikasikan KPI.');
        }

        $base = StudentKpi::where('semester_id', $semester->id)
            ->where('status', '!=', 'published');

        if ($class) {
            $base->where('class_id', $class->id);
        }

        $skipped = (clone $base)->whereNull('final_score')->count();
        $published = 0;

        foreach ((clone $base)->whereNotNull('final_score')->get() as $kpi) {
            $old = $kpi->status;
            $kpi->update([
                'status' => 'published',
                'published_by' => $by->id,
                'published_at' => now(),
            ]);
            $this->audit->log('published', $kpi, ['status' => $old], ['status' => 'published'], null, $by);
            $published++;
        }

        return ['published' => $published, 'skipped' => $skipped];
    }

    private function categoryFor(float $score): ?string
    {
        return KpiCategory::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->orderByDesc('min_score')->value('name');
    }

    private function previousScore(Student $student, Semester $semester): ?float
    {
        $previous = Semester::where('start_date', '<', $semester->start_date)
            ->orderByDesc('start_date')->first();

        if (! $previous) {
            return null;
        }

        $value = StudentKpi::where('student_id', $student->id)
            ->where('semester_id', $previous->id)
            ->where('status', 'published')
            ->value('final_score');

        return $value !== null ? (float) $value : null;
    }
}
