@extends('layouts.app')

@section('title', 'KPI Siswa')

@section('content')
@php
    $statusLabel = ['draft' => 'Draft', 'ready' => 'Diusulkan', 'published' => 'Dipublikasikan'];
    $statusClass = [
        'draft' => 'bg-slate-100 text-slate-700',
        'ready' => 'bg-amber-100 text-amber-800',
        'published' => 'bg-green-100 text-green-800',
    ];
    $num = fn ($v) => $v === null ? '-' : number_format($v, 1, ',', '.');
@endphp

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">KPI Siswa</h1>
        <p class="text-sm text-slate-500">Semester {{ $semester->label() }}</p>
    </div>

    <form method="GET" action="{{ route('kpi.index') }}">
        <select name="class" onchange="this.form.submit()" class="rounded border bg-white px-3 py-2 text-sm">
            <option value="">Semua kelas</option>
            @foreach($classes as $c)
                <option value="{{ $c->id }}" @selected($classId === $c->id)>Kelas {{ $c->name }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    <div class="bg-white border rounded-xl p-4"><p class="text-xs text-slate-500">Dihitung</p><p class="text-2xl font-semibold">{{ $stats['total'] }}</p></div>
    <div class="bg-white border rounded-xl p-4"><p class="text-xs text-slate-500">Lengkap</p><p class="text-2xl font-semibold">{{ $stats['complete'] }}</p></div>
    <div class="bg-white border rounded-xl p-4"><p class="text-xs text-slate-500">Belum lengkap</p><p class="text-2xl font-semibold text-amber-700">{{ $stats['incomplete'] }}</p></div>
    <div class="bg-white border rounded-xl p-4"><p class="text-xs text-slate-500">Dipublikasikan</p><p class="text-2xl font-semibold text-green-700">{{ $stats['published'] }}</p></div>
</div>

<div class="flex flex-wrap gap-3">
    <form method="POST" action="{{ route('kpi.hitung') }}">
        @csrf
        <input type="hidden" name="class" value="{{ $classId }}">
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">Hitung ulang KPI</button>
    </form>

    <form method="POST" action="{{ route('kpi.publikasi') }}"
          onsubmit="return confirm('Publikasikan semua KPI yang sudah lengkap{{ $classId ? ' pada kelas ini' : '' }}? Orang tua dan siswa akan dapat melihatnya.')">
        @csrf
        <input type="hidden" name="class" value="{{ $classId }}">
        <button type="submit" class="rounded-lg border border-green-600 px-4 py-2 text-sm text-green-700 hover:bg-green-50">Publikasikan yang lengkap</button>
    </form>
</div>

<div class="bg-white border rounded-xl overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500">
            <tr>
                <th class="px-4 py-3">Siswa</th>
                <th class="px-4 py-3">Kelas</th>
                <th class="px-4 py-3">Nilai</th>
                <th class="px-4 py-3">Hadir</th>
                <th class="px-4 py-3">Perilaku</th>
                <th class="px-4 py-3">Akhir</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kpis as $k)
                <tr class="border-t">
                    <td class="px-4 py-2">
                        <a href="{{ route('report.show', $k->student) }}" class="text-indigo-700 hover:underline">{{ $k->student->name }}</a>
                    </td>
                    <td class="px-4 py-2">{{ $k->schoolClass->name }}</td>
                    <td class="px-4 py-2">{{ $num($k->grade_score) }}</td>
                    <td class="px-4 py-2">{{ $num($k->attendance_score) }}</td>
                    <td class="px-4 py-2">{{ $num($k->behavior_score) }}</td>
                    <td class="px-4 py-2 font-semibold">
                        @if($k->final_score !== null)
                            {{ $num($k->final_score) }}
                        @else
                            <span class="text-amber-700 font-normal">belum lengkap</span>
                            @if($missing = data_get($k->breakdown, 'missing', []))
                                <span class="mt-1 block text-xs font-normal text-slate-500">Kurang: {{ implode(', ', array_map('ucfirst', $missing)) }}</span>
                            @endif
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $k->category ?? '-' }}</td>
                    <td class="px-4 py-2">
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $statusClass[$k->status] ?? '' }}">{{ $statusLabel[$k->status] ?? $k->status }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">Belum ada KPI. Klik "Hitung ulang KPI".</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
