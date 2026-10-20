<?php

namespace App\Http\Controllers;

use App\Models\GradeWeight;
use App\Models\BehaviorScore;
use App\Models\KpiCategory;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Subject;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(SettingService $settings): View
    {
        $semester = Semester::active();
        $weights = $semester
            ? GradeWeight::where('semester_id', $semester->id)->get()->keyBy('subject_id')
            : collect();

        return view('settings.index', [
            'semester' => $semester,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'subjectWeights' => $weights,
            'gradeWeights' => $settings->get('grade.default_weights', ['uh' => 40, 'uts' => 30, 'uas' => 30]),
            'defaultKkm' => $settings->get('grade.default_kkm', 75),
            'kpiWeights' => $settings->get('kpi.weights', ['grade' => 50, 'attendance' => 30, 'behavior' => 20]),
            'attendanceImpact' => $settings->get('kpi.attendance_impact', ['hadir' => 1, 'sakit' => 0.9, 'izin' => 0.9, 'alfa' => 0, 'bolos' => 0]),
            'gradeEditDays' => $settings->get('grade.edit_window_days', 7),
            'attendanceEditDays' => $settings->get('attendance.edit_window_days', 7),
            'behaviorScale' => $settings->get('behavior.scale_max', 4),
            'homeroomWeight' => $settings->get('behavior.homeroom_weight', 1),
            'kpiCategories' => KpiCategory::orderBy('min_score')->orderBy('sort_order')->get(),
        ]);
    }

    public function updateAcademicDefaults(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'grade_uh' => ['required', 'integer', 'between:0,100'],
            'grade_uts' => ['required', 'integer', 'between:0,100'],
            'grade_uas' => ['required', 'integer', 'between:0,100'],
            'default_kkm' => ['required', 'numeric', 'between:0,100'],
        ]);
        if ($data['grade_uh'] + $data['grade_uts'] + $data['grade_uas'] !== 100) {
            return back()->withErrors(['grade_total' => 'Total bobot UH, UTS, dan UAS harus 100%.'])->withInput();
        }

        $this->saveSetting('grade.default_weights', [
            'uh' => $data['grade_uh'], 'uts' => $data['grade_uts'], 'uas' => $data['grade_uas'],
        ], $audit);
        $this->saveSetting('grade.default_kkm', (float) $data['default_kkm'], $audit);

        return back()->with('status', 'Bobot nilai standar dan KKM berhasil diperbarui.');
    }

    public function updateKpiSettings(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'kpi_grade' => ['required', 'integer', 'between:0,100'],
            'kpi_attendance' => ['required', 'integer', 'between:0,100'],
            'kpi_behavior' => ['required', 'integer', 'between:0,100'],
            'impact_hadir' => ['required', 'numeric', 'between:0,1'],
            'impact_sakit' => ['required', 'numeric', 'between:0,1'],
            'impact_izin' => ['required', 'numeric', 'between:0,1'],
            'impact_alfa' => ['required', 'numeric', 'between:0,1'],
            'impact_bolos' => ['required', 'numeric', 'between:0,1'],
            'behavior_scale' => ['required', 'integer', 'between:2,10'],
            'homeroom_weight' => ['required', 'numeric', 'between:0,5'],
        ]);
        if ($data['kpi_grade'] + $data['kpi_attendance'] + $data['kpi_behavior'] !== 100) {
            return back()->withErrors(['kpi_total' => 'Total bobot nilai, kehadiran, dan perilaku harus 100%.'])->withInput();
        }
        if ($data['behavior_scale'] < (int) BehaviorScore::max('score')) {
            return back()->withErrors(['behavior_scale' => 'Skala maksimal tidak boleh lebih kecil dari skor perilaku yang sudah tersimpan.'])->withInput();
        }

        $this->saveSetting('kpi.weights', [
            'grade' => $data['kpi_grade'], 'attendance' => $data['kpi_attendance'], 'behavior' => $data['kpi_behavior'],
        ], $audit);
        $this->saveSetting('kpi.attendance_impact', [
            'hadir' => (float) $data['impact_hadir'], 'sakit' => (float) $data['impact_sakit'],
            'izin' => (float) $data['impact_izin'], 'alfa' => (float) $data['impact_alfa'],
            'bolos' => (float) $data['impact_bolos'],
        ], $audit);
        $this->saveSetting('behavior.scale_max', (int) $data['behavior_scale'], $audit);
        $this->saveSetting('behavior.homeroom_weight', (float) $data['homeroom_weight'], $audit);

        return back()->with('status', 'Bobot KPI dan pengaturan perilaku berhasil diperbarui.');
    }

    public function updateEditWindows(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'grade_edit_days' => ['required', 'integer', 'between:0,90'],
            'attendance_edit_days' => ['required', 'integer', 'between:0,90'],
        ]);

        $this->saveSetting('grade.edit_window_days', (int) $data['grade_edit_days'], $audit);
        $this->saveSetting('attendance.edit_window_days', (int) $data['attendance_edit_days'], $audit);

        return back()->with('status', 'Batas perubahan nilai dan absensi berhasil diperbarui.');
    }

    public function updateKpiCategories(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'categories' => ['required', 'array', 'min:1'],
            'categories.*.id' => ['required', 'integer', 'distinct', 'exists:kpi_categories,id'],
            'categories.*.name' => ['required', 'string', 'max:50'],
            'categories.*.min_score' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'categories.*.max_score' => ['required', 'numeric', 'between:0,100', 'decimal:0,2'],
            'categories.*.sort_order' => ['required', 'integer', 'between:0,255'],
            'deleted_ids' => ['nullable', 'array'],
            'deleted_ids.*' => ['required', 'integer', 'distinct', 'exists:kpi_categories,id'],
            'new_name' => ['nullable', 'required_with:new_min_score,new_max_score,new_sort_order', 'string', 'max:50'],
            'new_min_score' => ['nullable', 'required_with:new_name,new_max_score,new_sort_order', 'numeric', 'between:0,100', 'decimal:0,2'],
            'new_max_score' => ['nullable', 'required_with:new_name,new_min_score,new_sort_order', 'numeric', 'between:0,100', 'decimal:0,2'],
            'new_sort_order' => ['nullable', 'required_with:new_name,new_min_score,new_max_score', 'integer', 'between:0,255'],
        ]);

        $current = KpiCategory::all()->keyBy('id');
        $deletedIds = array_map('intval', $data['deleted_ids'] ?? []);
        $submittedIds = array_map(fn ($row) => (int) $row['id'], $data['categories']);
        $submittedIds = array_values(array_filter($submittedIds, fn ($id) => ! in_array($id, $deletedIds, true)));
        $expectedIds = $current->keys()->map(fn ($id) => (int) $id)->reject(fn ($id) => in_array($id, $deletedIds, true))->sort()->values()->all();
        sort($submittedIds);
        if ($submittedIds !== $expectedIds) {
            return back()->withErrors(['kpi_categories' => 'Daftar kategori berubah. Muat ulang halaman sebelum menyimpan.'])->withInput();
        }

        $finalRanges = collect($data['categories'])->reject(fn ($row) => in_array((int) $row['id'], $deletedIds, true))
            ->map(fn ($row) => ['name' => trim($row['name']), 'min_score' => (float) $row['min_score'], 'max_score' => (float) $row['max_score']]);
        if (filled($data['new_name'] ?? null)) {
            $finalRanges->push(['name' => trim($data['new_name']), 'min_score' => (float) $data['new_min_score'], 'max_score' => (float) $data['new_max_score']]);
        }

        $normalizedNames = $finalRanges->map(fn ($row) => mb_strtolower($row['name']))->all();
        if (count(array_unique($normalizedNames)) !== count($normalizedNames)) {
            return back()->withErrors(['kpi_categories' => 'Nama kategori tidak boleh sama.'])->withInput();
        }

        $expectedMinCents = 0;
        foreach ($finalRanges->sortBy('min_score') as $range) {
            $minCents = (int) round($range['min_score'] * 100);
            $maxCents = (int) round($range['max_score'] * 100);
            if ($minCents !== $expectedMinCents || $maxCents < $minCents) {
                return back()->withErrors(['kpi_categories' => 'Rentang kategori harus berurutan, tidak tumpang tindih, dan tidak memiliki celah.'])->withInput();
            }
            $expectedMinCents = $maxCents + 1;
        }
        if ($expectedMinCents !== 10001) {
            return back()->withErrors(['kpi_categories' => 'Rentang kategori harus mencakup skor 0 sampai 100.'])->withInput();
        }

        DB::transaction(function () use ($data, $deletedIds, $current, $audit): void {
            foreach ($data['categories'] as $row) {
                $category = $current->get((int) $row['id']);
                if (in_array((int) $row['id'], $deletedIds, true)) {
                    $audit->log('kpi_category.deleted', $category, $category->only(['name', 'min_score', 'max_score', 'sort_order']), null);
                    $category->delete();
                    continue;
                }
                $old = $category->only(['name', 'min_score', 'max_score', 'sort_order']);
                $category->update([
                    'name' => trim($row['name']), 'min_score' => $row['min_score'],
                    'max_score' => $row['max_score'], 'sort_order' => $row['sort_order'],
                ]);
                $audit->log('kpi_category.updated', $category, $old, $category->only(array_keys($old)));
            }

            if (filled($data['new_name'] ?? null)) {
                $category = KpiCategory::create([
                    'name' => trim($data['new_name']), 'min_score' => $data['new_min_score'],
                    'max_score' => $data['new_max_score'], 'sort_order' => $data['new_sort_order'],
                ]);
                $audit->log('kpi_category.created', $category, null, $category->only(['name', 'min_score', 'max_score', 'sort_order']));
            }
        });

        return back()->with('status', 'Kategori KPI berhasil diperbarui. Perhitungan berikutnya memakai rentang baru.');
    }

    public function updateSubjectWeight(Request $request, AuditLogger $audit): RedirectResponse
    {
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu untuk mengatur bobot per mata pelajaran.');

        $data = $request->validate([
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'use_default' => ['nullable', 'boolean'],
            'weight_uh' => ['required_unless:use_default,1', 'nullable', 'integer', 'between:0,100'],
            'weight_uts' => ['required_unless:use_default,1', 'nullable', 'integer', 'between:0,100'],
            'weight_uas' => ['required_unless:use_default,1', 'nullable', 'integer', 'between:0,100'],
            'kkm' => ['required_unless:use_default,1', 'nullable', 'numeric', 'between:0,100'],
        ]);

        $existing = GradeWeight::where('semester_id', $semester->id)->where('subject_id', $data['subject_id'])->first();
        if ($request->boolean('use_default')) {
            if ($existing) {
                $old = $existing->only(['weight_uh', 'weight_uts', 'weight_uas', 'kkm']);
                $existing->delete();
                $audit->log('grade_weight.reset_to_default', $existing, $old, null);
            }

            return back()->with('status', 'Bobot mata pelajaran kini mengikuti bobot standar sekolah.');
        }

        if ($data['weight_uh'] + $data['weight_uts'] + $data['weight_uas'] !== 100) {
            return back()->withErrors(['weight_total' => 'Total bobot mapel harus 100%.'])->withInput();
        }

        DB::transaction(function () use ($data, $semester, $existing, $audit): void {
            $weight = $existing ?? new GradeWeight(['semester_id' => $semester->id, 'subject_id' => $data['subject_id']]);
            $old = $weight->exists ? $weight->only(['weight_uh', 'weight_uts', 'weight_uas', 'kkm']) : null;
            $weight->fill([
                'weight_uh' => $data['weight_uh'], 'weight_uts' => $data['weight_uts'],
                'weight_uas' => $data['weight_uas'], 'kkm' => $data['kkm'],
            ])->save();
            $audit->log($old ? 'grade_weight.updated' : 'grade_weight.created', $weight, $old,
                $weight->only(['weight_uh', 'weight_uts', 'weight_uas', 'kkm']));
        });

        return back()->with('status', 'Bobot dan KKM mata pelajaran berhasil disimpan.');
    }

    private function saveSetting(string $key, mixed $value, AuditLogger $audit): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $old = $setting->exists ? $setting->value : null;
        $setting->value = $value;
        $setting->updated_by = $this->user()->id;
        $setting->save();
        $audit->log('setting.updated', $setting, ['key' => $key, 'value' => $old], ['key' => $key, 'value' => $setting->value]);
    }
}
