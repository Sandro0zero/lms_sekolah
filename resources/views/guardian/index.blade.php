@extends('layouts.app')

@section('title', 'Anak Saya')

@section('content')
    <div>
        <h1 class="text-xl font-semibold">Anak Saya</h1>
        <p class="text-sm text-slate-500">Semester {{ $semester->label() }}</p>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        @foreach($children as $child)
            <a href="{{ route('report.show', $child['student']) }}" class="bg-white border rounded-xl p-5 hover:border-indigo-400">
                <p class="font-semibold">{{ $child['student']->name }}</p>
                <p class="text-sm text-slate-500">NIS {{ $child['student']->nis }} · Kelas {{ $child['class']?->name ?? '-' }}</p>
                <p class="mt-3 text-sm text-indigo-700">Lihat nilai, kehadiran, dan KPI</p>
            </a>
        @endforeach
    </div>
@endsection