@extends('layouts.app')

@section('title', 'Beranda Admin · LMS Sekolah')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-xl font-semibold">Beranda Admin</h1>
        <p class="mt-1 text-sm text-slate-600">Ringkasan data dan pekerjaan pengelolaan sekolah.</p>
    </div>
    @if($semester)
        <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700">Semester {{ $semester->label() }}</span>
    @endif
</div>

@if(! $semester)
    <section class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        Belum ada semester aktif. Tambahkan dan aktifkan semester agar penempatan siswa, nilai, absensi, dan KPI dapat dikelola.
        <a href="{{ route('semesters.index') }}" class="ml-1 font-semibold underline">Kelola semester</a>
    </section>
@endif

<section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('master-data.index') }}" class="rounded-xl border bg-white p-4 hover:border-indigo-300">
        <p class="text-sm text-slate-500">Siswa aktif</p><p class="mt-1 text-2xl font-semibold">{{ $stats['students'] }}</p>
    </a>
    <a href="{{ route('master-data.index') }}" class="rounded-xl border bg-white p-4 hover:border-indigo-300">
        <p class="text-sm text-slate-500">Guru aktif</p><p class="mt-1 text-2xl font-semibold">{{ $stats['teachers'] }}</p>
    </a>
    <a href="{{ route('master-data.index') }}" class="rounded-xl border bg-white p-4 hover:border-indigo-300">
        <p class="text-sm text-slate-500">Orang tua/wali aktif</p><p class="mt-1 text-2xl font-semibold">{{ $stats['guardians'] }}</p>
    </a>
    <a href="{{ route('master-data.index') }}" class="rounded-xl border bg-white p-4 hover:border-indigo-300">
        <p class="text-sm text-slate-500">Kelas{{ $semester ? ' tahun ajaran aktif' : '' }}</p><p class="mt-1 text-2xl font-semibold">{{ $stats['classes'] }}</p>
    </a>
</section>

<section class="grid gap-5 lg:grid-cols-3">
    <div class="rounded-xl border bg-white p-5 lg:col-span-2">
        <div class="flex items-center justify-between gap-3">
            <div><h2 class="font-semibold">Perlu ditindaklanjuti</h2><p class="mt-1 text-xs text-slate-500">Kondisi data pada semester aktif.</p></div>
            @if($semester)<a href="{{ route('kpi.index') }}" class="text-sm text-indigo-700 hover:underline">Buka KPI</a>@endif
        </div>
        @if($semester)
            <div class="mt-4 divide-y">
                <a href="{{ route('class-roster.index') }}" class="flex items-center justify-between gap-3 py-3 text-sm hover:text-indigo-700">
                    <span>Siswa aktif belum ditempatkan di kelas</span><span class="font-semibold {{ $stats['unplaced_students'] ? 'text-amber-700' : 'text-green-700' }}">{{ $stats['unplaced_students'] }}</span>
                </a>
                <a href="{{ route('master-data.index') }}" class="flex items-center justify-between gap-3 py-3 text-sm hover:text-indigo-700">
                    <span>Kelas belum memiliki wali kelas</span><span class="font-semibold {{ $stats['classes_without_homeroom'] ? 'text-amber-700' : 'text-green-700' }}">{{ $stats['classes_without_homeroom'] }}</span>
                </a>
                <a href="{{ route('master-data.index') }}" class="flex items-center justify-between gap-3 py-3 text-sm hover:text-indigo-700">
                    <span>Kelas belum memiliki penugasan mapel</span><span class="font-semibold {{ $stats['classes_without_assignments'] ? 'text-amber-700' : 'text-green-700' }}">{{ $stats['classes_without_assignments'] }}</span>
                </a>
                <a href="{{ route('kpi.index') }}" class="flex items-center justify-between gap-3 py-3 text-sm hover:text-indigo-700">
                    <span>KPI belum lengkap</span><span class="font-semibold {{ $stats['kpi_incomplete'] ? 'text-amber-700' : 'text-green-700' }}">{{ $stats['kpi_incomplete'] }}</span>
                </a>
            </div>
        @else
            <p class="mt-4 text-sm text-slate-500">Aktifkan semester untuk melihat kesiapan kelas dan KPI.</p>
        @endif
    </div>

    <div class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold">Pintasan admin</h2>
        <div class="mt-4 grid gap-2">
            <a href="{{ route('master-data.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-indigo-700">Data Master</a>
            <a href="{{ route('class-roster.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">Tempatkan siswa · Wali Kelas</a>
            <a href="{{ route('semesters.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">Pengelolaan Semester</a>
            @if($semester)
                <a href="{{ route('kpi.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">KPI · {{ $stats['kpi_published'] }} dipublikasikan</a>
                <a href="{{ route('grades.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">Nilai dan Absensi</a>
            @endif
            <a href="{{ route('settings.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">Pengaturan sekolah</a>
            <a href="{{ route('audit.index') }}" class="rounded-lg border px-4 py-2.5 text-center text-sm font-medium hover:bg-slate-50">Riwayat perubahan</a>
        </div>
    </div>
</section>

@if($semester)
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div><h2 class="font-semibold">Kelas tahun ajaran aktif</h2><p class="mt-1 text-xs text-slate-500">Maksimal 8 kelas ditampilkan.</p></div>
            <a href="{{ route('master-data.index') }}" class="text-sm text-indigo-700 hover:underline">Kelola semua kelas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500"><tr><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Wali kelas</th><th class="px-5 py-3">Siswa aktif</th><th class="px-5 py-3">Penugasan</th></tr></thead>
                <tbody>
                    @forelse($classesList as $class)
                        <tr class="border-t"><td class="px-5 py-3 font-medium">{{ $class->name }}</td><td class="px-5 py-3">{{ $class->homeroomTeacher?->name ?? 'Belum ditentukan' }}</td><td class="px-5 py-3">{{ $class->active_students_count }}</td><td class="px-5 py-3">{{ $class->active_assignments_count }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada kelas untuk tahun ajaran ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endif
@endsection
