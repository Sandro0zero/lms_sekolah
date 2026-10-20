<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;

/** Menghitung KPI banyak siswa sekaligus (seluruh sekolah atau satu kelas). */
class KpiBatchService
{
    public function __construct(private KpiCalculator $calculator)
    {
    }

    public function calculate(Semester $semester, ?SchoolClass $class = null): array
    {
        $students = Student::whereHas('enrollments', function ($q) use ($semester, $class) {
            $q->where('academic_year', $semester->academic_year);
            if ($class) {
                $q->where('class_id', $class->id);
            }
        })->where('status', 'aktif')->get();

        $total = 0;
        $incomplete = 0;

        foreach ($students as $student) {
            $kpi = $this->calculator->calculate($student, $semester);
            if (! $kpi) {
                continue;
            }
            $total++;
            if ($kpi->final_score === null) {
                $incomplete++;
            }
        }

        return ['total' => $total, 'incomplete' => $incomplete];
    }
}
