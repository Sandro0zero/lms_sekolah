@extends('layouts.app')

@section('title', 'Penempatan Siswa · Wali Kelas')

@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Penempatan Siswa · Wali Kelas</h1>
    <p class="mt-1 text-sm text-slate-600">Pilih guru wali kelas, lalu tambahkan siswa yang sudah terdaftar ke kelasnya untuk tahun ajaran {{ $semester->academic_year }}.</p>
</div>

<section class="rounded-xl border bg-white p-5">
    <form method="GET" action="{{ route('class-roster.index') }}" class="grid gap-4 md:grid-cols-2 md:items-end">
        <div>
            <label for="roster_teacher" class="mb-1 block text-sm font-medium">1. Pilih guru wali kelas</label>
            <select id="roster_teacher" name="teacher_id" onchange="this.form.submit()" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                <option value="">Pilih guru</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected($selectedTeacherId === $teacher->id)>{{ $teacher->name }}</option>
                @endforeach
            </select>
        </div>
        @if($selectedTeacher)
            <div>
                <label for="roster_class" class="mb-1 block text-sm font-medium">2. Pilih kelas</label>
                @if($classes->count() === 1)
                    <p class="rounded-lg border bg-slate-50 px-3 py-2 text-sm">{{ $selectedClass?->name }} · wali kelas {{ $selectedTeacher->name }}</p>
                    <input type="hidden" name="class_id" value="{{ $selectedClass?->id }}">
                @else
                    <select id="roster_class" name="class_id" onchange="this.form.submit()" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                        <option value="">Pilih kelas yang diampu</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>Kelas {{ $class->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        @endif
    </form>

    @if($selectedTeacher && $classes->isEmpty())
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            Guru ini belum ditetapkan sebagai wali kelas pada tahun ajaran aktif. Tetapkan wali kelas melalui Data Master → Kelas.
        </div>
    @endif
</section>

@if($selectedClass)
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
            <div>
                <h2 class="font-semibold">3. Pilih siswa untuk {{ $selectedClass->name }}</h2>
                <p class="mt-1 text-xs text-slate-500">Siswa yang sudah berada di kelas ini ditandai. Jika siswa berada di kelas lain pada tahun ajaran yang sama, pilihan ini akan memindahkannya dan mencatat perubahan.</p>
            </div>
            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">{{ $students->count() }} siswa aktif</span>
        </div>

        @can('master.manage')
            <form method="POST" action="{{ route('class-roster.store') }}">
                @csrf
                <input type="hidden" name="teacher_id" value="{{ $selectedTeacher->id }}">
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <div class="max-h-[32rem] divide-y overflow-y-auto">
                    @forelse($students as $student)
                        @php($currentEnrollment = $student->enrollments->first())
                        <label class="flex cursor-pointer items-center gap-3 px-5 py-3 text-sm hover:bg-slate-50">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked($currentEnrollment?->class_id === $selectedClass->id)>
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium">{{ $student->name }}</span>
                                <span class="block text-xs text-slate-500">NIS {{ $student->nis }} · {{ $currentEnrollment?->schoolClass?->name ? 'Kelas saat ini '.$currentEnrollment->schoolClass->name : 'Belum ditempatkan pada tahun ajaran ini' }}</span>
                            </span>
                            @if($currentEnrollment?->class_id === $selectedClass->id)
                                <span class="text-xs text-green-700">Sudah di kelas ini</span>
                            @endif
                        </label>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">Belum ada siswa aktif yang bisa ditempatkan.</p>
                    @endforelse
                </div>
                @error('student_ids')<p class="px-5 pt-3 text-sm text-red-700">{{ $message }}</p>@enderror
                @error('class_id')<p class="px-5 pt-3 text-sm text-red-700">{{ $message }}</p>@enderror
                <div class="border-t px-5 py-4">
                    <button type="submit" @disabled($students->isEmpty()) class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Simpan penempatan siswa</button>
                </div>
            </form>
        @else
            <p class="px-5 py-6 text-sm text-slate-500">Akun Anda hanya dapat melihat penempatan. Admin dapat mengubah daftar siswa.</p>
        @endcan
    </section>
@elseif(! $selectedTeacher)
    <div class="rounded-xl border border-dashed bg-white p-8 text-center text-sm text-slate-500">Pilih guru wali kelas untuk melihat kelas dan daftar siswa.</div>
@endif
@endsection
