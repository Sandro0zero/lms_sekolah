@extends('layouts.app')

@section('title', 'Semester Belum Aktif · KPI Siswa')

@section('content')
<div class="mx-auto max-w-2xl rounded-xl border border-amber-200 bg-amber-50 p-6 sm:p-8">
    <p class="text-sm font-semibold text-amber-800">KPI Siswa</p>
    <h1 class="mt-2 text-xl font-semibold text-slate-900">Belum ada semester aktif</h1>
    <p class="mt-3 text-sm leading-6 text-slate-700">
        Halaman KPI siap digunakan, tetapi belum ada semester aktif di data sekolah. Aktifkan semester terlebih dahulu agar nilai, kehadiran, dan perilaku siswa dapat dihitung.
    </p>
    @can('master.view')
        <a href="{{ route('semesters.index') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buka pengelolaan semester</a>
    @endcan
</div>
@endsection
