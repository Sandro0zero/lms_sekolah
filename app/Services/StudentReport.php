<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\HomeroomNote;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentKpi;

/**
 * Menyusun data laporan satu siswa untuk satu semester.
 * Tampilan keluarga (orang tua/siswa): KPI hanya terlihat jika sudah dipublikasikan.
 * Tampilan staf: KPI draft ikut terlihat.
 */
class StudentReport
{
    public function __construct(
        private GradeCalculator $grades,
        private AttendanceCalculator $attendance,
        private SettingService $settings,
    ) {
    }

    public function build(Student $student, Semester $semester, bool $familyView, bool $isStudent = false): array
    {
        $class = $student->classForYear($semester->academic_year);

        $subjects = $this->grades->forStudent($student, $semester);

        $gradeRows = Grade::where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->orderBy('type')->orderBy('sequence')
            ->get()->groupBy('subject_id');

        $att = $this->attendance->forStudent($student, $semester);

        $absences = StudentAttendance::with('subject')
            ->where('student_id', $student->id)
            ->where('semester_id', $semester->id)
            ->where('status', '!=', 'hadir')
            ->orderByDesc('date')->orderBy('lesson_period')
            ->get();

        $kpi = StudentKpi::where('student_id', $student->id)
            ->where('semester_id', $semester->id)->first();

        $kpiVisible = $kpi !== null;
        if ($familyView) {
            $kpiVisible = $kpi !== null && $kpi->isPublished();
            if ($isStudent && ! $this->settings->get('kpi.student_can_view', true)) {
                $kpiVisible = false;
            }
        }

        $note = $kpiVisible
            ? HomeroomNote::where('student_id', $student->id)->where('semester_id', $semester->id)->first()
            : null;

        return compact(
            'student', 'semester', 'class', 'subjects', 'gradeRows',
            'att', 'absences', 'kpi', 'kpiVisible', 'note', 'familyView'
        );
    }
}