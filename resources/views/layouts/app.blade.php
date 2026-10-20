<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LMS Sekolah')</title>
    {{-- Tailwind via CDN: cukup untuk pengembangan, tanpa npm. Ganti dengan build sendiri saat produksi. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
@php
    $roleLabel = ['admin' => 'Admin', 'kepsek' => 'Kepala Sekolah', 'guru' => 'Guru', 'orangtua' => 'Orang Tua', 'siswa' => 'Siswa'];
@endphp
<header class="bg-white border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-6">
            <a href="{{ url('/') }}" class="font-semibold text-indigo-700">LMS Sekolah</a>
            @auth
                <nav class="flex gap-4 text-sm">
                    @if(auth()->user()->hasAnyRole('admin', 'kepsek'))
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('home') }}" class="hover:text-indigo-700">Beranda Admin</a>
                        @endif
                        <a href="{{ route('kpi.index') }}" class="hover:text-indigo-700">KPI Siswa</a>
                        @can('audit.view')<a href="{{ route('audit.index') }}" class="hover:text-indigo-700">Riwayat perubahan</a>@endcan
                        @can('master.view')
                            <a href="{{ route('master-data.index') }}" class="hover:text-indigo-700">Data Master</a>
                            <a href="{{ route('class-roster.index') }}" class="hover:text-indigo-700">Wali Kelas</a>
                            <a href="{{ route('semesters.index') }}" class="hover:text-indigo-700">Semester</a>
                            @can('settings.manage')
                                <a href="{{ route('settings.index') }}" class="hover:text-indigo-700">Pengaturan</a>
                            @endcan
                        @endcan
                    @elseif(auth()->user()->hasRole('guru'))
                        <a href="{{ route('guru.home') }}" class="hover:text-indigo-700">Beranda Guru</a>
                    @elseif(auth()->user()->hasRole('orangtua'))
                        <a href="{{ route('guardian.index') }}" class="hover:text-indigo-700">Anak Saya</a>
                    @elseif(auth()->user()->hasRole('siswa'))
                        <a href="{{ route('student.home') }}" class="hover:text-indigo-700">Rapor Saya</a>
                    @endif
                    @if(auth()->user()->hasAnyRole('admin', 'kepsek', 'guru'))
                        <a href="{{ route('grades.index') }}" class="hover:text-indigo-700">Nilai</a>
                        <a href="{{ route('attendance.index') }}" class="hover:text-indigo-700">Absensi</a>
                    @endif
                    @can('behavior.view')
                        <a href="{{ route('behavior.index') }}" class="hover:text-indigo-700">Perilaku</a>
                    @endcan
                </nav>
            @else
                <a href="{{ route('login') }}" class="text-sm text-indigo-700 hover:underline">Masuk</a>
            @endauth
        </div>
        <div class="flex items-center gap-3 text-sm">
            @auth
                <span>{{ auth()->user()->name }}
                    <span class="text-slate-500">({{ $roleLabel[auth()->user()->role] ?? auth()->user()->role }})</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded border px-2 py-1 text-xs hover:bg-slate-100">Keluar</button>
                </form>
            @endauth
            @if(app()->environment('local'))
                <a href="{{ route('demo.index') }}" class="rounded border px-2 py-1 text-xs hover:bg-slate-100">Ganti pengguna (demo)</a>
            @endif
        </div>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>

