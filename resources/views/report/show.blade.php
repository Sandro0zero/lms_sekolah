@extends('layouts.app')

@section('title', 'Rapor ' . $student->name)

@section('content')
@php
    $number = fn ($value, $precision = 1) => $value === null ? '—' : number_format((float) $value, $precision, ',', '.');
    $statuses = ['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alfa' => 'Alfa', 'bolos' => 'Bolos'];
    $backRoute = auth()->user()->hasRole('orangtua')
        ? route('guardian.index')
        : (auth()->user()->hasRole('guru') ? route('guru.home') : route('kpi.index'));
@endphp

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <a href="{{ $backRoute }}" class="text-sm text-indigo-700 hover:underline">← Kembali</a>
        <h1 class="mt-3 text-2xl font-semibold">Rapor Siswa</h1>
        <p class="mt-1 text-sm text-slate-500">Semester {{ $semester->label() }}</p>
    </div>
    <div class="rounded-lg border bg-white px-4 py-3 text-sm">
        <p class="font-semibold">{{ $student->name }}</p>
        <p class="mt-1 text-slate-500">NIS {{ $student->nis }} · Kelas {{ $class?->name ?? 'belum ditetapkan' }}</p>
    </div>
</div>

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">Rata-rata nilai</p>
        <p class="mt-2 text-2xl font-semibold">{{ $number($subjects->whereNotNull('final')->avg('final')) }}</p>
        <p class="mt-1 text-xs text-slate-500">Nilai akhir mapel yang lengkap</p>
    </div>
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">Skor kehadiran</p>
        <p class="mt-2 text-2xl font-semibold">{{ $number($att['score']) }}{{ $att['score'] !== null ? '%' : '' }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $att['total'] }} catatan pertemuan</p>
    </div>
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">Skor perilaku</p>
        <p class="mt-2 text-2xl font-semibold">{{ $kpiVisible ? $number($kpi?->behavior_score) : '—' }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ $kpiVisible ? 'Skala 0–100' : 'Belum tersedia' }}</p>
    </div>
    <div class="rounded-xl border bg-white p-4">
        <p class="text-xs text-slate-500">KPI semester</p>
        @if($kpiVisible && $kpi)
            <p class="mt-2 text-2xl font-semibold">{{ $number($kpi->final_score) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $kpi->category ?? 'Kategori belum ditetapkan' }}</p>
        @else
            <p class="mt-2 text-lg font-semibold">Belum tersedia</p>
            <p class="mt-1 text-xs text-slate-500">Hasil tampil setelah dipublikasikan</p>
        @endif
    </div>
</div>

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="border-b px-5 py-4">
        <h2 class="font-semibold">Nilai per mata pelajaran</h2>
        <p class="mt-1 text-sm text-slate-500">Rata-rata akhir dihitung dari UH, UTS, dan UAS sesuai bobot sekolah.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500">
                <tr>
                    <th class="px-5 py-3">Mata pelajaran</th>
                    <th class="px-4 py-3">Rata-rata UH</th>
                    <th class="px-4 py-3">UTS</th>
                    <th class="px-4 py-3">UAS</th>
                    <th class="px-4 py-3">Nilai akhir</th>
                    <th class="px-5 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                    <tr class="border-t">
                        <td class="px-5 py-3 font-medium">{{ $subject['subject'] }}</td>
                        <td class="px-4 py-3">{{ $number($subject['uh_avg']) }}</td>
                        <td class="px-4 py-3">{{ $number($subject['uts']) }}</td>
                        <td class="px-4 py-3">{{ $number($subject['uas']) }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $number($subject['final'], 0) }}</td>
                        <td class="px-5 py-3">
                            @if(!$subject['complete'])
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800">Belum lengkap</span>
                            @elseif($subject['passed'])
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">Tuntas · KKM {{ $number($subject['kkm']) }}</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2 py-1 text-xs text-red-800">Belum tuntas · KKM {{ $number($subject['kkm']) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada penugasan mata pelajaran untuk kelas pada semester ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="rounded-xl border bg-white">
        <div class="border-b px-5 py-4">
            <h2 class="font-semibold">Rekap kehadiran</h2>
            <p class="mt-1 text-sm text-slate-500">Ringkasan status pertemuan pada semester ini.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3">
            @foreach($statuses as $key => $label)
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">{{ $label }}</p>
                    <p class="mt-1 text-lg font-semibold">{{ $att['counts'][$key] ?? 0 }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-xl border bg-white">
        <div class="border-b px-5 py-4">
            <h2 class="font-semibold">Catatan wali kelas</h2>
            <p class="mt-1 text-sm text-slate-500">Catatan dibagikan bersama KPI yang sudah diterbitkan.</p>
        </div>
        <div class="p-5">
            @if($kpiVisible && $note)
                <p class="whitespace-pre-line text-sm leading-6">{{ $note->note }}</p>
            @elseif($kpiVisible)
                <p class="text-sm text-slate-500">Belum ada catatan wali kelas untuk semester ini.</p>
            @else
                <p class="text-sm text-slate-500">Catatan akan tersedia setelah KPI dipublikasikan.</p>
            @endif
        </div>
    </section>
</div>

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="border-b px-5 py-4">
        <h2 class="font-semibold">Riwayat ketidakhadiran</h2>
        <p class="mt-1 text-sm text-slate-500">Sakit, izin, alfa, dan bolos selama semester ini.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500">
                <tr><th class="px-5 py-3">Tanggal</th><th class="px-4 py-3">Mata pelajaran</th><th class="px-4 py-3">Pertemuan</th><th class="px-4 py-3">Status</th><th class="px-5 py-3">Keterangan</th></tr>
            </thead>
            <tbody>
                @forelse($absences as $absence)
                    <tr class="border-t">
                        <td class="px-5 py-3">{{ $absence->date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $absence->subject?->name ?? '—' }}</td>
                        <td class="px-4 py-3">Ke-{{ $absence->lesson_period }}</td>
                        <td class="px-4 py-3">{{ $statuses[$absence->status] ?? ucfirst($absence->status) }}</td>
                        <td class="px-5 py-3">{{ $absence->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada catatan ketidakhadiran pada semester ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
