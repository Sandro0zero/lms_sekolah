@extends('layouts.app')

@section('title', 'Penilaian Perilaku · LMS Sekolah')

@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Penilaian Perilaku</h1>
    <p class="mt-1 text-sm text-slate-600">Nilai aspek perilaku siswa untuk melengkapi komponen KPI semester.</p>
</div>

@if(! $semester)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Belum ada semester aktif. Minta admin mengaktifkan semester sebelum penilaian dibuka.</div>
@else
    @if($canConfigure)
        <div class="grid gap-5 lg:grid-cols-2">
            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-semibold">Periode penilaian · {{ $semester->label() }}</h2>
                <form method="POST" action="{{ route('behavior.period.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="opens_at" class="mb-1 block text-sm font-medium">Dibuka pada</label>
                        <input id="opens_at" name="opens_at" type="datetime-local" value="{{ old('opens_at', $period?->opens_at?->format('Y-m-d\TH:i')) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('opens_at')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="closes_at" class="mb-1 block text-sm font-medium">Ditutup pada</label>
                        <input id="closes_at" name="closes_at" type="datetime-local" value="{{ old('closes_at', $period?->closes_at?->format('Y-m-d\TH:i')) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('closes_at')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm sm:col-span-2">
                        <input name="is_locked" type="checkbox" value="1" @checked(old('is_locked', $period?->is_locked)) class="rounded border-slate-300 text-indigo-600">
                        Kunci periode secara manual
                    </label>
                    <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 sm:col-span-2">Simpan periode</button>
                </form>
            </section>

            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-semibold">Aspek penilaian</h2>
                <div class="mt-3 divide-y">
                    @forelse($configuredAspects as $aspect)
                        <div class="py-3">
                            <form method="POST" action="{{ route('behavior.aspects.update', $aspect) }}" class="grid gap-2 sm:grid-cols-[1fr_6rem_8rem_auto] sm:items-center">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $aspect->name }}" required maxlength="100" @readonly($aspect->scores_count > 0) class="w-full rounded-lg border px-3 py-2 text-sm read-only:bg-slate-50" aria-label="Nama aspek">
                                <input name="sort_order" type="number" min="0" max="255" value="{{ $aspect->sort_order }}" required class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Urutan aspek">
                                <select name="is_active" class="w-full rounded-lg border bg-white px-3 py-2 text-sm" aria-label="Status aspek">
                                    <option value="1" @selected($aspect->is_active)>Aktif</option><option value="0" @selected(! $aspect->is_active)>Nonaktif</option>
                                </select>
                                <button class="rounded-lg border px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">Simpan</button>
                            </form>
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>{{ $aspect->scores_count }} skor tersimpan</span>
                                @if($aspect->scores_count > 0)<span>Nama dikunci agar arti riwayat lama tidak berubah.</span>@endif
                                @error('aspect_name_'.$aspect->id)<span class="text-red-700">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada aspek aktif.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('behavior.aspects.store') }}" class="mt-5 flex flex-wrap items-end gap-3 border-t pt-4">
                    @csrf
                    <div class="min-w-48 flex-1">
                        <label for="aspect_name" class="mb-1 block text-sm font-medium">Tambah aspek</label>
                        <input id="aspect_name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kemandirian" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="w-28">
                        <label for="sort_order" class="mb-1 block text-sm font-medium">Urutan</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="255" value="{{ old('sort_order', $configuredAspects->count() + 1) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                    <button class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-slate-50">Tambah</button>
                </form>
            </section>
        </div>
    @endif

    <section class="rounded-xl border bg-white p-5">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="font-semibold">Penilaian guru</h2>
                <p class="mt-1 text-sm text-slate-500">Semester {{ $semester->label() }} · skala 1–{{ $scaleMax }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $period?->isOpen() ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                {{ $period?->isOpen() ? 'Periode dibuka' : 'Periode belum dibuka atau sudah ditutup' }}
            </span>
        </div>

        @if($classes->isEmpty())
            <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Belum ada kelas yang dapat dinilai. Pastikan kelas, siswa, semester, dan penugasan guru sudah disiapkan.</p>
        @else
            <form method="GET" action="{{ route('behavior.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
                <div class="min-w-56">
                    <label for="class" class="mb-1 block text-sm font-medium">Pilih kelas</label>
                    <select id="class" name="class" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                        <option value="">Pilih kelas</option>
                        @foreach($classes as $class)<option value="{{ $class->id }}" @selected($selectedClass?->id === $class->id)>Kelas {{ $class->name }}</option>@endforeach
                    </select>
                </div>
                <button class="rounded-lg border px-4 py-2 text-sm font-medium hover:bg-slate-50">Tampilkan siswa</button>
            </form>
        @endif
    </section>

    @if($selectedClass)
        @if(! $period)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Admin perlu mengatur periode penilaian sebelum guru dapat mengisi aspek perilaku.</div>
        @elseif($aspects->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Belum ada aspek aktif. Minta admin menambahkan aspek penilaian.</div>
        @elseif($canAssess)
            <form method="POST" action="{{ route('behavior.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <section class="space-y-4">
                    @forelse($students as $student)
                        @php($assessment = $assessments->get($student->id))
                        @php($existingScores = $assessment?->scores?->keyBy('aspect_id') ?? collect())
                        <article class="rounded-xl border bg-white p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div><h3 class="font-semibold">{{ $student->name }}</h3><p class="text-xs text-slate-500">NIS {{ $student->nis }}</p></div>
                                @if($assessment?->submitted_at)<span class="text-xs text-green-700">Tersimpan {{ $assessment->submitted_at->format('d/m/Y H:i') }}</span>@endif
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($aspects as $aspect)
                                    <div>
                                        <label for="score_{{ $student->id }}_{{ $aspect->id }}" class="mb-1 block text-sm font-medium">{{ $aspect->name }}</label>
                                        <select id="score_{{ $student->id }}_{{ $aspect->id }}" name="scores[{{ $student->id }}][{{ $aspect->id }}]" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                                            <option value="">Pilih skor</option>
                                            @for($score = 1; $score <= $scaleMax; $score++)
                                                <option value="{{ $score }}" @selected((string) old('scores.'.$student->id.'.'.$aspect->id, $existingScores->get($aspect->id)?->score) === (string) $score)>{{ $score }} dari {{ $scaleMax }}</option>
                                            @endfor
                                        </select>
                                        @error('scores.'.$student->id.'.'.$aspect->id)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label for="note_{{ $student->id }}" class="mb-1 block text-sm font-medium">Catatan internal (opsional)</label>
                                <textarea id="note_{{ $student->id }}" name="notes[{{ $student->id }}]" rows="2" maxlength="1000" class="w-full rounded-lg border px-3 py-2 text-sm">{{ old('notes.'.$student->id, $assessment?->note) }}</textarea>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border bg-white p-6 text-center text-sm text-slate-500">Belum ada siswa aktif di kelas ini.</div>
                    @endforelse
                </section>
                @if($students->isNotEmpty())
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">Jika mulai menilai seorang siswa, semua aspek wajib diisi. Catatan hanya untuk kebutuhan internal sekolah.</p>
                        <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Simpan penilaian</button>
                    </div>
                @endif
            </form>
        @elseif($students->isNotEmpty())
            <section class="overflow-x-auto rounded-xl border bg-white">
                <div class="border-b px-5 py-4">
                    <h2 class="font-semibold">Hasil penilaian · {{ $selectedClass->name }}</h2>
                    @if($period && ! $period->isOpen())<p class="mt-1 text-sm text-slate-500">Periode tidak sedang dibuka; data hanya dapat dilihat.</p>@endif
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Guru penilai</th><th class="px-4 py-3">Skor per aspek</th><th class="px-4 py-3">Dikirim</th></tr></thead>
                    <tbody>
                        @forelse($reviewAssessments as $review)
                            <tr class="border-t align-top">
                                <td class="px-4 py-3 font-medium">{{ $review->student->name }}</td>
                                <td class="px-4 py-3">{{ $review->teacher->name }}</td>
                                <td class="px-4 py-3"><div class="flex flex-wrap gap-1.5">@foreach($review->scores as $score)<span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $score->aspect->name }}: {{ $score->score }}/{{ $scaleMax }}</span>@endforeach</div></td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $review->submitted_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada penilaian yang dikirim untuk kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endif
    @endif
@endif
@endsection
