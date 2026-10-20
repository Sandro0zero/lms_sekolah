<?php

namespace App\Console\Commands;

use App\Models\Semester;
use App\Models\Student;
use App\Services\KpiCalculator;
use Illuminate\Console\Command;

class HitungKpi extends Command
{
    protected $signature = 'lms:hitung-kpi {--semester= : ID semester (default: semester aktif)}';

    protected $description = 'Hitung KPI siswa untuk satu semester (hasil berstatus draft, belum dipublikasikan)';

    public function handle(KpiCalculator $calculator): int
    {
        $semester = $this->option('semester')
            ? Semester::find($this->option('semester'))
            : Semester::active();

        if (! $semester) {
            $this->error('Semester tidak ditemukan.');

            return self::FAILURE;
        }

        $this->info('Menghitung KPI semester ' . $semester->label());

        $students = Student::whereHas('enrollments', fn ($q) => $q->where('academic_year', $semester->academic_year))
            ->orderBy('name')->get();

        $rows = [];
        $incomplete = 0;

        foreach ($students as $student) {
            $kpi = $calculator->calculate($student, $semester);
            if (! $kpi) {
                continue;
            }

            if ($kpi->final_score === null) {
                $incomplete++;
            }

            $rows[] = [
                $student->nis,
                $student->name,
                $kpi->grade_score ?? '-',
                $kpi->attendance_score ?? '-',
                $kpi->behavior_score ?? '-',
                $kpi->final_score ?? 'belum lengkap',
                $kpi->category ?? '-',
            ];
        }

        $this->table(['NIS', 'Nama', 'Nilai', 'Hadir', 'Perilaku', 'Akhir', 'Kategori'], $rows);
        $this->info(count($rows) . " siswa dihitung, {$incomplete} belum lengkap.");

        return self::SUCCESS;
    }
}