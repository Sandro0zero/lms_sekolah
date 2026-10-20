@extends('layouts.app')

@section('title', 'Absensi Siswa · LMS Sekolah')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Absensi Siswa</h1>
        <p class="mt-1 text-sm text-slate-600">Catat kehadiran per kelas, mata pelajaran, tanggal, dan jam pelajaran.</p>
    </div>
    <a href="{{ route('grades.index') }}" class="rounded-lg border bg-white px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-50">Buka nilai</a>
</div>

@if(! $semester)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Belum ada semester aktif. Minta admin mengaktifkan semester sebelum absensi dapat diinput.</div>
@elseif($assignments->isEmpty())
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">Belum ada penugasan mengajar untuk semester {{ $semester->label() }}.</div>
@else
    <section class="rounded-xl border bg-white p-5">
        <form method="GET" action="{{ route('attendance.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
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
                <label for="date" class="mb-1 block text-sm font-medium">Tanggal</label>
                <input id="date" name="date" type="date" value="{{ $date }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
            </div>
            <div>
                <label for="period" class="mb-1 block text-sm font-medium">Jam pelajaran</label>
                <input id="period" name="period" type="number" min="1" max="20" value="{{ $period }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
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
        @if(! $dateInSemester)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Tanggal harus berada dalam periode semester aktif ({{ $semester->start_date->format('d/m/Y') }}–{{ $semester->end_date->format('d/m/Y') }}).</div>
        @endif

        <form method="POST" action="{{ route('attendance.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
            <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="period" value="{{ $period }}">

            @if(auth()->user()->hasRole('admin'))
                <div class="rounded-xl border bg-white p-4">
                    <label for="reason" class="mb-1 block text-sm font-medium">Alasan koreksi (jika lewat 7 hari)</label>
                    <input id="reason" name="reason" value="{{ old('reason') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            @endif

            @if($canEdit && $students->isNotEmpty())
                <div class="flex justify-end">
                    <button id="mark-all-present" type="button" class="rounded-lg border border-green-600 bg-white px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-50">Tandai semua hadir</button>
                </div>
            @endif

            <section class="overflow-x-auto rounded-xl border bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">NIS</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Keterangan jika tidak hadir</th></tr></thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr class="border-t align-top">
                                <td class="px-4 py-3 font-medium">{{ $student->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $student->nis }}</td>
                                <td class="px-4 py-3">
                                    <select name="statuses[{{ $student->id }}]" class="w-36 rounded-lg border bg-white px-2 py-2 text-sm" aria-label="Status {{ $student->name }}">
                                        <option value="">Belum dicatat</option>
                                        @foreach(['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alfa' => 'Alfa', 'bolos' => 'Bolos'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('statuses.'.$student->id, $student->current_attendance?->status) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('statuses.'.$student->id)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input name="notes[{{ $student->id }}]" value="{{ old('notes.'.$student->id, $student->current_attendance?->note) }}" maxlength="255" placeholder="Contoh: demam" class="w-full min-w-48 rounded-lg border px-3 py-2 text-sm" aria-label="Keterangan {{ $student->name }}">
                                    @error('notes.'.$student->id)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                                    @if($student->current_attendance)<p class="mt-1 text-xs text-slate-500">Dicatat {{ $student->current_attendance->created_at->format('d/m/Y H:i') }}</p>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            @if($canEdit && $students->isNotEmpty())
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">Absensi dapat diubah guru dalam 7 hari sejak input pertama. Keterangan wajib untuk sakit, izin, alfa, dan bolos.</p>
                    <button @disabled(! $dateInSemester || $date > now()->toDateString()) class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Simpan absensi</button>
                </div>
            @elseif(! $canEdit)
                <p class="text-sm text-slate-500">Anda memiliki akses lihat saja.</p>
            @endif
        </form>

        @if($canEdit && $students->isNotEmpty())
            <script>
                document.getElementById('mark-all-present')?.addEventListener('click', () => {
                    document.querySelectorAll('select[name^="statuses["]').forEach(select => select.value = 'hadir');
                    document.querySelectorAll('input[name^="notes["]').forEach(input => input.value = '');
                });
            </script>
        @endif
    @endif
@endif
@endsection
