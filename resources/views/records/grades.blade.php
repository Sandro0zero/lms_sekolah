@extends('layouts.app')

@section('title', 'Input Nilai · LMS Sekolah')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Nilai Siswa</h1>
        <p class="mt-1 text-sm text-slate-600">Nilai mentah disimpan per ulangan. Nilai akhir dihitung otomatis dari bobot sekolah.</p>
    </div>
    <a href="{{ route('attendance.index') }}" class="rounded-lg border bg-white px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-50">Buka absensi</a>
</div>

@if(! $semester)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Belum ada semester aktif. Minta admin mengaktifkan semester sebelum nilai dapat diinput.</div>
@elseif($assignments->isEmpty())
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Belum ada penugasan mengajar untuk semester {{ $semester->label() }}.</div>
@else
    <section class="rounded-xl border bg-white p-5">
        <form method="GET" action="{{ route('grades.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="class" class="mb-1 block text-sm font-medium">Kelas</label>
                <select id="class" name="class" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                    <option value="">Pilih kelas</option>
                    @foreach($assignments->unique('class_id') as $assignment)<option value="{{ $assignment->class_id }}" @selected(request('class') == $assignment->class_id)>{{ $assignment->schoolClass->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="subject" class="mb-1 block text-sm font-medium">Mata pelajaran</label>
                <select id="subject" name="subject" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                    <option value="">Pilih mapel</option>
                    @foreach($assignments->unique(fn ($assignment) => $assignment->class_id.'-'.$assignment->subject_id) as $assignment)<option value="{{ $assignment->subject_id }}" data-class-id="{{ $assignment->class_id }}" @selected(request('class') == $assignment->class_id && request('subject') == $assignment->subject_id)>{{ $assignment->subject->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="type" class="mb-1 block text-sm font-medium">Jenis nilai</label>
                <select id="type" name="type" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                    <option value="uh" @selected($type === 'uh')>Ulangan harian</option><option value="uts" @selected($type === 'uts')>UTS</option><option value="uas" @selected($type === 'uas')>UAS</option>
                </select>
            </div>
            <div>
                <label for="sequence" class="mb-1 block text-sm font-medium">Urutan UH</label>
                <input id="sequence" name="sequence" type="number" min="1" max="99" value="{{ $sequence }}" class="w-full rounded-lg border px-3 py-2 text-sm">
            </div>
            <div class="flex items-end"><button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tampilkan siswa</button></div>
        </form>
    </section>

    <script>
        (() => {
            const classSelect = document.getElementById('class');
            const subjectSelect = document.getElementById('subject');
            const updateSubjects = () => {
                const classId = classSelect.value;
                Array.from(subjectSelect.options).forEach(option => {
                    if (option.value) option.hidden = option.dataset.classId !== classId;
                });
                if (subjectSelect.selectedOptions[0]?.hidden) subjectSelect.value = '';
            };
            classSelect.addEventListener('change', updateSubjects);
            updateSubjects();
        })();
    </script>

    @if($selectedClass && $selectedSubject)
        <form method="POST" action="{{ route('grades.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="sequence" value="{{ $sequence }}">
            <section class="grid gap-3 rounded-xl border bg-white p-5 sm:grid-cols-3">
                <div>
                    <label for="title" class="mb-1 block text-sm font-medium">Judul penilaian</label>
                    <input id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: UH Bab 1" class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('title')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="assessed_on" class="mb-1 block text-sm font-medium">Tanggal penilaian</label>
                    <input id="assessed_on" name="assessed_on" type="date" min="{{ $semester->start_date->toDateString() }}" max="{{ $semester->end_date->toDateString() }}" value="{{ old('assessed_on', $defaultDate) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('assessed_on')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                @if(auth()->user()->hasRole('admin'))
                    <div>
                        <label for="reason" class="mb-1 block text-sm font-medium">Alasan koreksi (jika lewat 7 hari)</label>
                        <input id="reason" name="reason" value="{{ old('reason') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endif
            </section>

            <section class="overflow-x-auto rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">NIS</th><th class="px-4 py-3">Nilai 0–100</th><th class="px-4 py-3">Terakhir diubah</th></tr></thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr class="border-t">
                                <td class="px-4 py-3 font-medium">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $student->nis }}</td>
                                <td class="px-4 py-3">
                                    <input name="scores[{{ $student->id }}]" type="number" min="0" max="100" step="0.01" value="{{ old('scores.'.$student->id, $student->current_grade?->score) }}" class="w-32 rounded-lg border px-3 py-2 text-sm" aria-label="Nilai {{ $student->name }}">
                                    @error('scores.'.$student->id)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $student->current_grade?->updated_at?->format('d/m/Y H:i') ?? 'Belum diinput' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            @if($canEdit && $students->isNotEmpty())
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">Perubahan nilai guru dibatasi 7 hari sejak input pertama. Nilai mentah disimpan tanpa pembulatan.</p>
                    <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Simpan nilai</button>
                </div>
            @elseif(! $canEdit)
                <p class="text-sm text-slate-500">Anda memiliki akses lihat saja.</p>
            @endif
        </form>
    @endif
@endif
@endsection
