<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\LearningResource;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LearningResourceController extends Controller
{
    public function materials(Request $request): View
    {
        return $this->listing($request, 'material');
    }

    public function library(Request $request): View
    {
        $this->assertLibraryAccess($this->user());

        return $this->listing($request, 'library');
    }

    public function storeMaterial(Request $request): RedirectResponse
    {
        abort_unless($this->user()->hasAnyRole('admin', 'guru'), 403);

        return $this->saveNew($request, 'material');
    }

    public function storeLibrary(Request $request): RedirectResponse
    {
        abort_unless($this->user()->hasRole('admin'), 403);

        return $this->saveNew($request, 'library');
    }

    public function updateMaterial(Request $request, LearningResource $resource): RedirectResponse
    {
        abort_unless($resource->kind === 'material' && $this->user()->hasRole('admin'), 403);

        return $this->saveChanges($request, $resource, 'material');
    }

    public function updateLibrary(Request $request, LearningResource $resource): RedirectResponse
    {
        abort_unless($resource->kind === 'library' && $this->user()->hasRole('admin'), 403);

        return $this->saveChanges($request, $resource, 'library');
    }

    public function destroyMaterial(LearningResource $resource): RedirectResponse
    {
        abort_unless($resource->kind === 'material' && $this->user()->hasRole('admin'), 403);

        return $this->remove($resource, 'materials');
    }

    public function destroyLibrary(LearningResource $resource): RedirectResponse
    {
        abort_unless($resource->kind === 'library' && $this->user()->hasRole('admin'), 403);

        return $this->remove($resource, 'library');
    }

    public function openMaterial(LearningResource $resource): StreamedResponse
    {
        return $this->stream($resource, 'material');
    }

    public function openLibrary(LearningResource $resource): StreamedResponse
    {
        $this->assertLibraryAccess($this->user());

        return $this->stream($resource, 'library');
    }

    private function listing(Request $request, string $kind): View
    {
        $user = $this->user();
        $activeSemester = Semester::active();
        $query = ($kind === 'library' ? LearningResource::query() : $this->visibleResources($user))
            ->where('kind', $kind)
            ->with(['semester', 'schoolClass', 'subject', 'uploader'])->latest();

        $classIds = $this->availableClassIds($user, $activeSemester);
        $classes = $classIds === null
            ? SchoolClass::query()
            : SchoolClass::whereIn('id', $classIds);
        if ($user->hasAnyRole('admin', 'kepsek') && $activeSemester) {
            $classes->where('academic_year', $activeSemester->academic_year);
        }
        $classes = $classes->orderBy('academic_year')->orderBy('name')->get();
        $selectedClassId = $request->filled('class_id') && $classes->contains('id', (int) $request->query('class_id'))
            ? (int) $request->query('class_id')
            : null;

        if ($kind === 'material' && $selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        if ($request->filled('type') && $kind === 'library' && in_array($request->query('type'), ['ebook', 'article'], true)) {
            $query->where('library_type', $request->query('type'));
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $query->where(fn (Builder $builder) => $builder
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->orWhere('category', 'like', '%'.$term.'%'));
        }

        $assignmentQuery = TeachingAssignment::with(['schoolClass', 'subject', 'semester']);
        if ($user->hasRole('guru')) {
            $assignmentQuery->where('teacher_id', $user->teacher?->id);
            if ($activeSemester) {
                $assignmentQuery->where('semester_id', $activeSemester->id);
            }
            if ($selectedClassId) {
                $assignmentQuery->where('class_id', $selectedClassId);
            }
        }
        $assignments = $kind === 'material' && $user->hasRole('guru') ? $assignmentQuery->latest()->get() : collect();

        if ($kind === 'material' && $selectedClassId && $user->hasRole('guru')) {
            $subjectIds = $assignments->pluck('subject_id')->unique();
            $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
        } elseif ($kind === 'material' && ! $user->hasAnyRole('admin', 'kepsek')) {
            $subjectQuery = $this->visibleResources($user)->where('kind', 'material')->whereNotNull('subject_id');
            if ($selectedClassId) {
                $subjectQuery->where('class_id', $selectedClassId);
            }
            $subjects = Subject::whereIn('id', $subjectQuery->select('subject_id'))->orderBy('name')->get();
        } else {
            $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        }

        $selectedSubjectId = $request->filled('subject_id') && $subjects->contains('id', (int) $request->query('subject_id'))
            ? (int) $request->query('subject_id')
            : null;
        if ($kind === 'material' && $selectedSubjectId) {
            $query->where('subject_id', $selectedSubjectId);
        }

        return view('learning-resources.index', [
            'resources' => $query->paginate(12)->withQueryString(),
            'pageKind' => $kind,
            'classes' => $classes,
            'subjects' => $subjects,
            'assignments' => $assignments,
            'selectedClassId' => $selectedClassId,
            'selectedSubjectId' => $selectedSubjectId,
            'activeSemester' => $activeSemester,
            'canManage' => $user->hasRole('admin'),
            'canUpload' => $user->hasRole('admin') || ($kind === 'material' && $user->hasRole('guru') && $activeSemester && $selectedClassId && $assignments->isNotEmpty()),
        ]);
    }

    private function saveNew(Request $request, string $kind): RedirectResponse
    {
        $user = $this->user();
        $data = $request->validate($this->rules($kind));

        if ($kind === 'material' && $user->hasRole('guru')) {
            $activeSemester = Semester::active();
            abort_unless($activeSemester, 422, 'Aktifkan semester terlebih dahulu sebelum mengunggah materi.');
            $assignment = TeachingAssignment::query()
                ->where('teacher_id', $user->teacher?->id)
                ->where('class_id', $data['class_id'] ?? null)
                ->where('subject_id', $data['subject_id'] ?? null)
                ->where('semester_id', $activeSemester?->id)
                ->firstOrFail();
            $data['semester_id'] = $assignment->semester_id;
        } elseif ($kind === 'material') {
            $data['semester_id'] = Semester::active()?->id;
        }

        $file = $data['file'];
        $path = $file->store('learning-resources', 'local');
        abort_unless(is_string($path), 500, 'File gagal disimpan. Silakan coba lagi.');

        try {
            LearningResource::create($this->attributes($data, $file, $path, $kind) + ['uploaded_by' => $user->id]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        return to_route($kind === 'material' ? 'learning-resources.materials' : 'learning-resources.library')
            ->with('status', $kind === 'material' ? 'Materi berhasil diunggah.' : 'Dokumen library berhasil diunggah.');
    }

    private function saveChanges(Request $request, LearningResource $resource, string $kind): RedirectResponse
    {
        $data = $request->validate($this->rules($kind, false));
        $oldPath = $resource->file_path;
        $path = null;

        if (isset($data['file'])) {
            $file = $data['file'];
            $path = $file->store('learning-resources', 'local');
            abort_unless(is_string($path), 500, 'File pengganti gagal disimpan. Silakan coba lagi.');
            $resource->fill($this->attributes($data, $file, $path, $kind));
        } else {
            $resource->fill($this->attributes($data, kind: $kind));
        }

        try {
            $resource->save();
        } catch (\Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }

        if ($path && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return to_route($kind === 'material' ? 'learning-resources.materials' : 'learning-resources.library')
            ->with('status', 'Dokumen berhasil diperbarui.');
    }

    private function remove(LearningResource $resource, string $route): RedirectResponse
    {
        Storage::disk('local')->delete($resource->file_path);
        $resource->delete();

        return to_route('learning-resources.'.$route)->with('status', 'Dokumen berhasil dihapus.');
    }

    private function stream(LearningResource $resource, string $kind): StreamedResponse
    {
        abort_unless($resource->kind === $kind, 404);
        $user = $this->user();
        if ($kind === 'library') {
            $this->assertLibraryAccess($user);
        } else {
            abort_unless($this->visibleResources($user)->whereKey($resource->id)->exists(), 403);
        }
        abort_unless(Storage::disk('local')->exists($resource->file_path), 404);

        return Storage::disk('local')->response(
            $resource->file_path,
            $resource->original_filename,
            ['Content-Type' => $resource->mime_type],
            $resource->mime_type === 'application/pdf' ? 'inline' : 'attachment'
        );
    }

    private function assertLibraryAccess(User $user): void
    {
        abort_if($user->hasRole('orangtua'), 403);
        abort_unless($user->hasAnyRole('admin', 'kepsek', 'guru', 'siswa'), 403);
    }

    private function rules(string $kind, bool $requireFile = true): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:100'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480', function (string $attribute, mixed $value, \Closure $fail) use ($kind): void {
                if ($value && $kind === 'library' && strtolower($value->getClientOriginalExtension()) !== 'pdf') {
                    $fail('Library hanya menerima file PDF.');
                }
            }],
        ];

        if ($kind === 'library') {
            $rules['library_type'] = ['required', Rule::in(['ebook', 'article'])];
        }

        if ($this->user()->hasRole('admin')) {
            $rules += [
                'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
                'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
                'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            ];
        } elseif ($kind === 'material') {
            $rules['class_id'] = [$this->user()->hasRole('guru') ? 'required' : 'nullable', 'integer', 'exists:school_classes,id'];
            $rules['subject_id'] = [$this->user()->hasRole('guru') ? 'required' : 'nullable', 'integer', 'exists:subjects,id'];
        }

        return $rules;
    }

    private function attributes(array $data, mixed $file = null, ?string $path, string $kind): array
    {
        $attributes = [
            'kind' => $kind,
            'library_type' => $kind === 'library' ? $data['library_type'] : null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
        ];

        foreach (['semester_id', 'class_id', 'subject_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $data[$field];
            }
        }

        if ($file && $path) {
            $attributes += [
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => $file->getSize(),
            ];
        }

        return $attributes;
    }

    private function visibleResources(User $user): Builder
    {
        $query = LearningResource::query();

        if ($user->hasAnyRole('admin', 'kepsek')) {
            return $query;
        }

        $activeSemester = Semester::active();
        $query->where(function (Builder $builder) use ($activeSemester): void {
            $builder->whereNull('semester_id');
            if ($activeSemester) {
                $builder->orWhere('semester_id', $activeSemester->id);
            }
        });

        if ($user->hasRole('guru')) {
            $teacherId = $user->teacher?->id;
            if (! $teacherId) {
                return $query->whereNull('class_id');
            }

            return $query->where(function (Builder $builder) use ($teacherId): void {
                $builder->whereNull('class_id')->orWhereExists(function ($subquery) use ($teacherId): void {
                    $subquery->selectRaw('1')->from('teaching_assignments')
                        ->where('teaching_assignments.teacher_id', $teacherId)
                        ->whereColumn('teaching_assignments.class_id', 'learning_resources.class_id')
                        ->where(function ($subjectQuery) {
                            $subjectQuery->whereNull('learning_resources.subject_id')
                                ->orWhereColumn('teaching_assignments.subject_id', 'learning_resources.subject_id');
                        })
                        ->where(function ($semesterQuery) {
                            $semesterQuery->whereColumn('teaching_assignments.semester_id', 'learning_resources.semester_id')
                                ->orWhereNull('learning_resources.semester_id');
                        });
                });
            });
        }

        $studentIds = collect();
        if ($user->hasRole('siswa') && $user->student) {
            $studentIds->push($user->student->id);
        } elseif ($user->hasRole('orangtua') && $user->guardian) {
            $studentIds = $user->guardian->students()->pluck('students.id');
        }

        $year = $activeSemester?->academic_year;
        $classIds = $studentIds->isEmpty() || ! $year
            ? collect()
            : Enrollment::whereIn('student_id', $studentIds)->where('academic_year', $year)->pluck('class_id');

        return $query->where(function (Builder $builder) use ($classIds, $activeSemester): void {
            $builder->whereNull('class_id');
            if ($classIds->isNotEmpty()) {
                $builder->orWhere(function (Builder $scoped) use ($classIds, $activeSemester): void {
                    $scoped->whereIn('class_id', $classIds)
                        ->where(function (Builder $semesterScope) use ($activeSemester): void {
                            $semesterScope->whereNull('semester_id');
                            if ($activeSemester) {
                                $semesterScope->orWhere('semester_id', $activeSemester->id);
                            }
                        });
                });
            }
        });
    }

    private function availableClassIds(User $user, ?Semester $activeSemester): ?Collection
    {
        if ($user->hasAnyRole('admin', 'kepsek')) {
            return null;
        }

        if ($user->hasRole('guru')) {
            return TeachingAssignment::query()
                ->where('teacher_id', $user->teacher?->id)
                ->when($activeSemester, fn (Builder $query) => $query->where('semester_id', $activeSemester->id))
                ->distinct()->pluck('class_id');
        }

        $studentIds = collect();
        if ($user->hasRole('siswa') && $user->student) {
            $studentIds->push($user->student->id);
        } elseif ($user->hasRole('orangtua') && $user->guardian) {
            $studentIds = $user->guardian->students()->pluck('students.id');
        }

        $enrollments = Enrollment::query()->whereIn('student_id', $studentIds);
        if ($activeSemester) {
            $enrollments->where('academic_year', $activeSemester->academic_year);
        }

        return $enrollments->distinct()->pluck('class_id');
    }
}
