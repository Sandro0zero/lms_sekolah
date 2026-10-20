@extends('layouts.app')

@section('title', 'Beranda Guru')

@section('content')
    <div class="bg-white border rounded-xl p-5">
        <h1 class="text-xl font-semibold">Selamat datang, {{ $teacher->name }}</h1>
        <p class="text-sm text-slate-500">Semester {{ $semester->label() }}</p>
    </div>

    <section class="bg-white border rounded-xl p-5">
        <h2 class="font-semibold mb-3">Kelas dan mata pelajaran yang diajar</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-left text-slate-500">
                    <tr><th class="py-2 pr-4">Kelas</th><th class="py-2">Mata pelajaran</th></tr>
                </thead>
                <tbody>
                    @forelse($assignments as $a)
                        <tr class="border-t">
                            <td class="py-2 pr-4">{{ $a->schoolClass->name }}</td>
                            <td class="py-2">{{ $a->subject->name }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-3 text-slate-500">Belum ada penugasan mengajar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-slate-500">Input absensi siswa, check-in guru, dan input nilai hadir di modul berikutnya.</p>
    </section>

    @foreach($homeroomClasses as $class)
        <section class="bg-white border rounded-xl p-5">
            <h2 class="font-semibold mb-1">Wali kelas {{ $class->name }}</h2>
            <p class="text-sm text-slate-500 mb-3">Klik nama siswa untuk melihat nilai, kehadiran, dan KPI.</p>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($class->students->sortBy('name') as $s)
                    <a href="{{ route('report.show', $s) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-indigo-50">
                        {{ $s->name }} <span class="text-slate-400">· {{ $s->nis }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection