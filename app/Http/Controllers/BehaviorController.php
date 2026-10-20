<?php

namespace App\Http\Controllers;

use App\Models\BehaviorAspect;
use App\Models\BehaviorAssessment;
use App\Models\BehaviorPeriod;
use App\Models\BehaviorScore;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BehaviorController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->user();
        $semester = Semester::active();
        $period = $semester ? BehaviorPeriod::where('semester_id', $semester->id)->first() : null;
        $classes = $semester ? $this->classesFor($user, $semester) : collect();
        $class = $classes->firstWhere('id', $request->integer('class'));
        if ($request->filled('class')) {
            abort_unless($class, 403, 'Anda tidak memiliki akses ke kelas tersebut.');
        }

        $aspects = BehaviorAspect::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $students = $class && $semester ? $this->roster($class, $semester) : collect();
        $assessments = collect();
        $reviewAssessments = collect();
        if ($students->isNotEmpty() && $user->teacher) {
            $assessments = BehaviorAssessment::with('scores')
                ->where('semester_id', $semester->id)
                ->where('teacher_id', $user->teacher->id)
                ->whereIn('student_id', $students->modelKeys())
                ->get()->keyBy('student_id');
        }
        if ($students->isNotEmpty()) {
            $reviewQuery = BehaviorAssessment::with(['student', 'teacher', 'scores.aspect'])
                ->where('semester_id', $semester->id)
                ->whereIn('student_id', $students->modelKeys());
            if ($user->hasRole('guru')) {
                $reviewQuery->where('teacher_id', $user->teacher?->id);
            }
            $reviewAssessments = $reviewQuery->orderBy('student_id')->get();
        }

        return view('behavior.index', [
            'semester' => $semester,
            'period' => $period,
            'classes' => $classes,
            'selectedClass' => $class,
            'aspects' => $aspects,
            'configuredAspects' => BehaviorAspect::withCount('scores')->orderBy('sort_order')->orderBy('name')->get(),
            'students' => $students,
            'assessments' => $assessments,
            'reviewAssessments' => $reviewAssessments,
            'scaleMax' => app(SettingService::class)->get('behavior.scale_max', 4),
            'canAssess' => $user->hasRole('guru') && $user->teacher && $user->can('behavior.manage')
                && $period?->isOpen() && $aspects->isNotEmpty(),
            'canConfigure' => $user->can('settings.manage'),
        ]);
    }

    public function save(Request $request, SettingService $settings, AuditLogger $audit): RedirectResponse
    {
        $user = $this->user();
        abort_unless($user->hasRole('guru') && $user->teacher && $user->can('behavior.manage'), 403);
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu.');
        $period = BehaviorPeriod::where('semester_id', $semester->id)->first();
        abort_unless($period?->isOpen(), 403, 'Periode penilaian perilaku belum dibuka atau sudah ditutup.');

        $max = (int) $settings->get('behavior.scale_max', 4);
        $data = $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'scores' => ['required', 'array'],
            'scores.*' => ['array'],
            'scores.*.*' => ['nullable', 'integer', 'between:1,'.$max],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $class = $this->classesFor($user, $semester)->firstWhere('id', (int) $data['class_id']);
        abort_unless($class, 403, 'Anda tidak memiliki akses ke kelas tersebut.');
        $roster = $this->roster($class, $semester);
        $aspects = BehaviorAspect::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        abort_if($aspects->isEmpty(), 422, 'Belum ada aspek penilaian yang aktif.');

        $studentIds = array_map('intval', $roster->modelKeys());
        $aspectIds = array_map('intval', $aspects->modelKeys());
        abort_unless(array_diff(array_map('intval', array_keys($data['scores'])), $studentIds) === [], 422, 'Daftar siswa tidak cocok dengan kelas yang dipilih.');

        DB::transaction(function () use ($data, $semester, $class, $user, $audit, $roster, $aspects, $studentIds, $aspectIds): void {
            foreach ($data['scores'] as $studentId => $rowScores) {
                $studentId = (int) $studentId;
                abort_unless(in_array($studentId, $studentIds, true), 422, 'Siswa bukan anggota kelas ini.');
                abort_unless(array_diff(array_map('intval', array_keys($rowScores)), $aspectIds) === [], 422, 'Aspek penilaian tidak valid.');

                $filledScores = array_filter($rowScores, fn ($score) => $score !== null && $score !== '');
                if ($filledScores === []) {
                    continue;
                }

                foreach ($aspects as $aspect) {
                    abort_unless(isset($rowScores[$aspect->id]) && $rowScores[$aspect->id] !== '', 422,
                        "Semua aspek harus diisi untuk siswa {$roster->firstWhere('id', $studentId)->name}.");
                }

                $assessment = BehaviorAssessment::firstOrNew([
                    'semester_id' => $semester->id,
                    'student_id' => $studentId,
                    'teacher_id' => $user->teacher->id,
                ]);
                $old = $assessment->exists ? [
                    'note' => $assessment->note,
                    'scores' => BehaviorScore::where('assessment_id', $assessment->id)->pluck('score', 'aspect_id')->all(),
                ] : null;
                $assessment->note = $data['notes'][$studentId] ?? null;
                $assessment->submitted_at = now();
                $assessment->save();

                foreach ($aspects as $aspect) {
                    BehaviorScore::updateOrCreate(
                        ['assessment_id' => $assessment->id, 'aspect_id' => $aspect->id],
                        ['score' => (int) $rowScores[$aspect->id]],
                    );
                }

                $audit->log($old ? 'behavior_assessment.updated' : 'behavior_assessment.submitted', $assessment, $old, [
                    'note' => $assessment->note,
                    'scores' => array_map('intval', $rowScores),
                ]);
            }
        });

        return redirect()->route('behavior.index', ['class' => $class->id])->with('status', 'Penilaian perilaku berhasil disimpan.');
    }

    public function savePeriod(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($this->user()->can('settings.manage'), 403);
        $semester = Semester::active();
        abort_unless($semester, 422, 'Aktifkan semester terlebih dahulu.');

        $data = $request->validate([
            'opens_at' => ['required', 'date', 'after_or_equal:'.$semester->start_date->toDateString()],
            'closes_at' => ['required', 'date', 'after:opens_at', 'before_or_equal:'.$semester->end_date->toDateString().' 23:59:59'],
            'is_locked' => ['nullable', 'boolean'],
        ]);

        $period = DB::transaction(function () use ($data, $semester, $request, $audit): BehaviorPeriod {
            $period = BehaviorPeriod::firstOrNew(['semester_id' => $semester->id]);
            $old = $period->exists ? $period->only(['opens_at', 'closes_at', 'is_locked']) : null;
            $period->fill([
                'opens_at' => $data['opens_at'],
                'closes_at' => $data['closes_at'],
                'is_locked' => $request->boolean('is_locked'),
            ]);
            $period->save();
            $audit->log($old ? 'behavior_period.updated' : 'behavior_period.created', $period, $old,
                $period->only(['opens_at', 'closes_at', 'is_locked']));

            return $period;
        });

        return redirect()->route('behavior.index')->with('status', 'Periode penilaian perilaku berhasil disimpan.');
    }

    public function storeAspect(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($this->user()->can('settings.manage'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:behavior_aspects,name'],
            'sort_order' => ['required', 'integer', 'between:0,255'],
        ]);
        $aspect = BehaviorAspect::create([...$data, 'is_active' => true]);
        $audit->log('behavior_aspect.created', $aspect, null, $aspect->only(['name', 'sort_order', 'is_active']));

        return redirect()->route('behavior.index')->with('status', 'Aspek perilaku berhasil ditambahkan.');
    }

    public function updateAspect(Request $request, BehaviorAspect $aspect, AuditLogger $audit): RedirectResponse
    {
        abort_unless($this->user()->can('settings.manage'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('behavior_aspects', 'name')->ignore($aspect->id)],
            'sort_order' => ['required', 'integer', 'between:0,255'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($aspect->scores()->exists() && $data['name'] !== $aspect->name) {
            return back()->withErrors(['aspect_name_'.$aspect->id => 'Nama aspek tidak dapat diubah karena sudah dipakai dalam penilaian. Nonaktifkan aspek atau ubah urutannya agar riwayat tetap konsisten.']);
        }

        $old = $aspect->only(['name', 'sort_order', 'is_active']);
        $aspect->update([...$data, 'is_active' => (bool) $data['is_active']]);
        $audit->log('behavior_aspect.updated', $aspect, $old, $aspect->only(array_keys($old)));

        return redirect()->route('behavior.index')->with('status', 'Pengaturan aspek perilaku berhasil diperbarui.');
    }

    private function classesFor($user, Semester $semester)
    {
        $query = SchoolClass::where('academic_year', $semester->academic_year);
        if ($user->hasRole('guru')) {
            abort_unless($user->teacher, 403);
            $ids = TeachingAssignment::where('semester_id', $semester->id)
                ->where('teacher_id', $user->teacher->id)->pluck('class_id')
                ->merge($user->teacher->homeroomClasses()->where('academic_year', $semester->academic_year)->pluck('id'))
                ->unique()->values();
            $query->whereIn('id', $ids);
        } elseif (! $user->hasAnyRole('admin', 'kepsek')) {
            abort(403);
        }

        return $query->orderBy('name')->get();
    }

    private function roster(SchoolClass $class, Semester $semester)
    {
        return Student::where('status', 'aktif')->whereHas('enrollments', fn ($query) => $query
            ->where('class_id', $class->id)->where('academic_year', $semester->academic_year))
            ->orderBy('name')->get();
    }
}
