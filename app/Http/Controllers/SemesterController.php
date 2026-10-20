<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function index(): View
    {
        return view('semesters.index', [
            'semesters' => Semester::query()->orderByDesc('academic_year')->orderByDesc('term')->get(),
            'canManage' => $this->user()->can('master.manage'),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'term' => ['required', 'integer', 'in:1,2'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'academic_year.regex' => 'Tahun ajaran harus memakai format YYYY/YYYY, misalnya 2026/2027.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        [$firstYear, $secondYear] = array_map('intval', explode('/', $data['academic_year']));
        $startYear = (int) date('Y', strtotime($data['start_date']));
        $endYear = (int) date('Y', strtotime($data['end_date']));
        if ($secondYear !== $firstYear + 1
            || $startYear < $firstYear || $startYear > $secondYear
            || $endYear < $firstYear || $endYear > $secondYear) {
            return back()->withErrors([
                'academic_year' => 'Tahun ajaran harus berurutan dan tanggal semester harus berada di dalam rentang tahun tersebut.',
            ])->withInput();
        }

        $request->validate([
            'academic_year' => [Rule::unique('semesters')->where('term', $data['term'])],
        ], ['academic_year.unique' => 'Semester ini sudah terdaftar.']);

        $semester = DB::transaction(function () use ($data, $request, $audit): Semester {
            $isActive = $request->boolean('is_active');
            if ($isActive) {
                Semester::query()->where('is_active', true)->update(['is_active' => false]);
            }

            $semester = Semester::create([
                'academic_year' => $data['academic_year'],
                'term' => (int) $data['term'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $isActive,
            ]);

            $audit->log('semester.created', $semester, null, $semester->only([
                'academic_year', 'term', 'start_date', 'end_date', 'is_active',
            ]));

            return $semester;
        });

        return redirect()->route('semesters.index')->with('status', "Semester {$semester->label()} berhasil ditambahkan.");
    }

    public function activate(Semester $semester, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($semester, $audit): void {
            $previous = Semester::active();
            Semester::query()->where('is_active', true)->update(['is_active' => false]);
            $semester->update(['is_active' => true]);

            $audit->log('semester.activated', $semester,
                ['previous_active_semester_id' => $previous?->id],
                ['active_semester_id' => $semester->id, 'label' => $semester->label()]);
        });

        return redirect()->route('semesters.index')->with('status', "Semester {$semester->label()} sekarang aktif.");
    }
}
