<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LMS Sekolah')</title>
    {{-- Tailwind via CDN: cukup untuk pengembangan, tanpa npm. Ganti dengan build sendiri saat produksi. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
@php
    $roleLabel = ['admin' => 'Admin', 'kepsek' => 'Kepala Sekolah', 'guru' => 'Guru', 'orangtua' => 'Orang Tua', 'siswa' => 'Siswa'];
    $user = auth()->user();
    $navClass = 'shrink-0 rounded-lg px-3 py-2 text-sm font-medium transition';
    $navActive = 'bg-indigo-50 text-indigo-700';
    $navIdle = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';
@endphp
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-2.5 rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <span class="grid size-9 place-items-center rounded-xl bg-indigo-600 text-sm font-bold text-white">LS</span>
            <span><span class="block text-sm font-bold leading-tight">LMS Sekolah</span><span class="block text-xs text-slate-500">Sistem informasi akademik</span></span>
        </a>
        <div class="flex items-center gap-2 sm:gap-3">
            @auth
                <div class="hidden text-right sm:block"><p class="max-w-48 truncate text-sm font-semibold text-slate-800">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $roleLabel[$user->role] ?? $user->role }}</p></div>
                @if(app()->environment('local'))
                    <a href="{{ route('demo.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">Ganti pengguna</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Masuk</a>
            @endauth
        </div>
    </div>
    @auth
        <nav aria-label="Navigasi utama" class="border-t border-slate-100">
            <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 py-2 [scrollbar-width:thin] sm:px-6 lg:px-8">
                @if($user->hasRole('admin'))
                    <a href="{{ route('home') }}" @class([$navClass, request()->routeIs('home') ? $navActive : $navIdle]) @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                @elseif($user->hasRole('kepsek'))
                    <a href="{{ route('kpi.index') }}" @class([$navClass, request()->routeIs('kpi.*') ? $navActive : $navIdle]) @if(request()->routeIs('kpi.*')) aria-current="page" @endif>KPI Siswa</a>
                @elseif($user->hasRole('guru'))
                    <a href="{{ route('guru.home') }}" @class([$navClass, request()->routeIs('guru.*') ? $navActive : $navIdle])>Beranda Guru</a>
                @elseif($user->hasRole('orangtua'))
                    <a href="{{ route('guardian.index') }}" @class([$navClass, request()->routeIs('guardian.*') ? $navActive : $navIdle])>Anak Saya</a>
                @elseif($user->hasRole('siswa'))
                    <a href="{{ route('student.home') }}" @class([$navClass, request()->routeIs('student.*') ? $navActive : $navIdle])>Rapor Saya</a>
                @endif
                @if($user->hasAnyRole('admin', 'kepsek'))
                    @can('master.view')
                        <a href="{{ route('master-data.index') }}" @class([$navClass, request()->routeIs('master-data.index') ? $navActive : $navIdle]) @if(request()->routeIs('master-data.index')) aria-current="page" @endif>Data Master</a>
                        <a href="{{ route('class-roster.index') }}" @class([$navClass, request()->routeIs('class-roster.*') ? $navActive : $navIdle]) @if(request()->routeIs('class-roster.*')) aria-current="page" @endif>Wali Kelas</a>
                        <a href="{{ route('semesters.index') }}" @class([$navClass, request()->routeIs('semesters.*') ? $navActive : $navIdle]) @if(request()->routeIs('semesters.*')) aria-current="page" @endif>Semester</a>
                    @endcan
                    @can('settings.manage')<a href="{{ route('settings.index') }}" @class([$navClass, request()->routeIs('settings.*') ? $navActive : $navIdle]) @if(request()->routeIs('settings.*')) aria-current="page" @endif>Pengaturan</a>@endcan
                    @can('audit.view')<a href="{{ route('audit.index') }}" @class([$navClass, request()->routeIs('audit.*') ? $navActive : $navIdle]) @if(request()->routeIs('audit.*')) aria-current="page" @endif>Riwayat</a>@endcan
                @endif
                @if($user->hasAnyRole('admin', 'kepsek', 'guru'))
                    <a href="{{ route('grades.index') }}" @class([$navClass, request()->routeIs('grades.*') ? $navActive : $navIdle]) @if(request()->routeIs('grades.*')) aria-current="page" @endif>Nilai</a>
                    <a href="{{ route('attendance.index') }}" @class([$navClass, request()->routeIs('attendance.*') ? $navActive : $navIdle]) @if(request()->routeIs('attendance.*')) aria-current="page" @endif>Absensi</a>
                @endif
                @can('behavior.view')<a href="{{ route('behavior.index') }}" @class([$navClass, request()->routeIs('behavior.*') ? $navActive : $navIdle]) @if(request()->routeIs('behavior.*')) aria-current="page" @endif>Perilaku</a>@endcan
            </div>
        </nav>
    @endauth
</header>

<main class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    @if(session('status'))
        <div role="status" class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm"><span class="mt-0.5 font-bold">✓</span><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm">
            <p class="mb-1 font-semibold">Periksa kembali data yang dimasukkan</p>
            @foreach($errors->all() as $error)
                <p class="mt-1">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>

