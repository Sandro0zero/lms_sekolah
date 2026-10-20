<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Support\Collection;

/**
 * Nilai akhir berbobot per mapel:
 *   (rata-rata UH x bobot UH + UTS x bobot UTS + UAS x bobot UAS) / 100
 * Nilai akhir tidak disimpan di database, selalu dihitung dari nilai mentah.
 */
class GradeCalculator
{
    public function __construct(private SettingService $settings)
    {
    }

    /** Bobot + KKM untuk mapel di semester ini; jatuh ke default sekolah jika belum diatur. */
    public function weightsFor(Semester $semester, int $subjectId): array
    {
        $row = GradeWeight::where('semester_id', $semester->id)
            ->where('subject_id', $subjectId)->first();

        if ($row) {
            return [
                'uh' => $row->weight_uh,
                'uts' => $row->weight_uts,
                'uas' => $row->weight_uas,
                'kkm' => $row->kkm,
            ];
        }

        $w = $this->settings->get('grade.default_weights', ['uh' => 40, 'uts' => 30, 'uas' => 30]);

        return [
            'uh' => $w['uh'],
            'uts' => $w['uts'],
            'uas' => $w['uas'],
            'kkm' => (float) $this->settings->get('grade.default_kkm', 75),
        ];
    }

    public function forSubject(Student $student, Subject $subject, Semester $semester): array
    {
        $grades = Grade::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('semester_id', $semester->id)
            ->get();

        $uh = $grades->where('type', 'uh')->avg('score');
        $uts = $grades->firstWhere('type', 'uts')?->score;
        $uas = $grades->firstWhere('type', 'uas')?->score;

        $w = $this->weightsFor($semester, $subject->id);
        $complete = $uh !== null && $uts !== null && $uas !== null;

        $final = $complete
            ? (int) round(($uh * $w['uh'] + $uts * $w['uts'] + $uas * $w['uas']) / 100)
            : null;

        return [
            'subject_id' => $subject->id,
            'subject' => $subject->name,
            'uh_avg' => $uh !== null ? round($uh, 2) : null,
            'uts' => $uts,
            'uas' => $uas,
            'final' => $final,          // null = belum lengkap (UH/UTS/UAS ada yang kosong)
            'kkm' => $w['kkm'],
            'complete' => $complete,
            'passed' => $final !== null ? $final >= $w['kkm'] : null,
        ];
    }

    /** Nilai akhir semua mapel yang diajarkan di kelas siswa pada semester ini. */
    public function forStudent(Student $student, Semester $semester): Collection
    {
        $class = $student->classForYear($semester->academic_year);

        if (! $class) {
            return collect();
        }

        $subjectIds = TeachingAssignment::where('class_id', $class->id)
            ->where('semester_id', $semester->id)
            ->pluck('subject_id');

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get()
            ->map(fn (Subject $s) => $this->forSubject($student, $s, $semester));
    }
}