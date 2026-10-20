<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentKpi;
use App\Services\KpiBatchService;
use App\Services\KpiCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless($this->user()->hasAnyRole('admin', 'kepsek'), 403);
    }

    private function semester(): Semester
    {
        $semester = Semester::active();
        abort_unless($semester, 404, 'Belum ada semester aktif.');

        return $semester;
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();
        $semester = Semester::active();

        if (! $semester) {
            return response()->view('kpi.no-semester');
        }

        $classes = SchoolClass::where('academic_year', $semester->academic_year)->orderBy('name')->get();
        $classId = $this->validatedClassId($request, $semester);

        $kpis = StudentKpi::with(['student', 'schoolClass'])
            ->where('semester_id', $semester->id)
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->get()
            ->sortBy(fn ($k) => $k->schoolClass->name . '|' . $k->student->name);

        $stats = [
            'total' => $kpis->count(),
            'complete' => $kpis->whereNotNull('final_score')->count(),
            'incomplete' => $kpis->whereNull('final_score')->count(),
            'published' => $kpis->where('status', 'published')->count(),
        ];

        return view('kpi.index', compact('semester', 'classes', 'classId', 'kpis', 'stats'));
    }

    public function hitung(Request $request, KpiBatchService $batch)
    {
        $this->authorizeStaff();
        $semester = $this->semester();
        $classId = $this->validatedClassId($request, $semester);
        $class = $classId ? SchoolClass::findOrFail($classId) : null;

        $result = $batch->calculate($semester, $class);

        return redirect()->route('kpi.index', ['class' => $classId])
            ->with('status', "KPI dihitung untuk {$result['total']} siswa ({$result['incomplete']} belum lengkap).");
    }

    public function publikasi(Request $request, KpiCalculator $calculator)
    {
        $this->authorizeStaff();
        $semester = $this->semester();
        $classId = $this->validatedClassId($request, $semester);
        $class = $classId ? SchoolClass::findOrFail($classId) : null;

        $result = $calculator->publish($semester, $request->user(), $class);

        return redirect()->route('kpi.index', ['class' => $classId])
            ->with('status', "{$result['published']} KPI dipublikasikan, {$result['skipped']} dilewati karena belum lengkap.");
    }

    private function validatedClassId(Request $request, Semester $semester): ?int
    {
        $validated = $request->validate([
            'class' => [
                'nullable',
                'integer',
                Rule::exists('school_classes', 'id')->where('academic_year', $semester->academic_year),
            ],
        ]);

        return isset($validated['class']) ? (int) $validated['class'] : null;
    }
}
