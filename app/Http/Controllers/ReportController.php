<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\Student;
use App\Services\StudentReport;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function show(Student $student, StudentReport $report)
    {
        Gate::authorize('view', $student);

        $user = $this->user();
        $semester = Semester::active();
        abort_unless($semester, 404, 'Belum ada semester aktif.');

        $data = $report->build(
            $student,
            $semester,
            familyView: $user->hasAnyRole('orangtua', 'siswa'),
            isStudent: $user->hasRole('siswa'),
        );

        return view('report.show', $data);
    }

    public function me()
    {
        $student = $this->user()->student;
        abort_unless($student, 404);

        return redirect()->route('report.show', $student);
    }
}
