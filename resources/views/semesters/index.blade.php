@extends('layouts.app')

@section('title', 'Semester · LMS Sekolah')

@section('content')
<div>
    <h1 class="text-xl font-semibold">Semester</h1>
    <p class="mt-1 text-sm text-slate-600">Atur tahun ajaran dan semester yang menjadi dasar pencatatan nilai, kehadiran, serta KPI.</p>
</div>

@if($canManage)
    <section class="rounded-xl border bg-white p-5">
        <h2 class="font-semibold">Tambah semester</h2>
        <form method="POST" action="{{ route('semesters.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <div>
                <label for="academic_year" class="mb-1 block text-sm font-medium">Tahun ajaran</label>
                <input id="academic_year" name="academic_year" value="{{ old('academic_year') }}" placeholder="2026/2027" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('academic_year')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="term" class="mb-1 block text-sm font-medium">Semester</label>
                <select id="term" name="term" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">Pilih semester</option>
                    <option value="1" @selected(old('term') == '1')>Ganjil</option>
                    <option value="2" @selected(old('term') == '2')>Genap</option>
                </select>
                @error('term')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="start_date" class="mb-1 block text-sm font-medium">Tanggal mulai</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('start_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="end_date" class="mb-1 block text-sm font-medium">Tanggal selesai</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('end_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-col justify-end gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input name="is_active" type="checkbox" value="1" @checked(old('is_active')) class="rounded border-slate-300 text-indigo-600">
                    Jadikan semester aktif
                </label>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan semester</button>
            </div>
        </form>
    </section>
@endif

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="border-b px-5 py-4">
        <h2 class="font-semibold">Daftar semester</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Tahun ajaran</th>
                    <th class="px-5 py-3 font-medium">Semester</th>
                    <th class="px-5 py-3 font-medium">Periode</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    @if($canManage)<th class="px-5 py-3 font-medium">Aksi</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($semesters as $semester)
                    <tr class="border-t">
                        <td class="px-5 py-3">{{ $semester->academic_year }}</td>
                        <td class="px-5 py-3">{{ $semester->term === 1 ? 'Ganjil' : 'Genap' }}</td>
                        <td class="px-5 py-3">{{ $semester->start_date->format('d/m/Y') }} – {{ $semester->end_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            @if($semester->is_active)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">Arsip</span>
                            @endif
                        </td>
                        @if($canManage)
                            <td class="px-5 py-3">
                                @unless($semester->is_active)
                                    <form method="POST" action="{{ route('semesters.activate', $semester) }}" onsubmit="return confirm('Aktifkan {{ $semester->label() }}? Semester aktif saat ini akan diganti.')">
                                        @csrf
                                        <button class="rounded border border-indigo-200 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">Aktifkan</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Sedang digunakan</span>
                                @endunless
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canManage ? 5 : 4 }}" class="px-5 py-8 text-center text-slate-500">Belum ada semester. Tambahkan tahun ajaran dan periode untuk mulai mengelola KPI.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
