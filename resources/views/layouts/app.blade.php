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
    $navClass = 'relative flex w-full shrink-0 items-center rounded-xl px-3.5 py-3 text-sm font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500';
    $navActive = 'bg-indigo-50 text-indigo-700 after:absolute after:inset-y-2 after:left-0 after:w-1 after:rounded-full after:bg-indigo-600';
    $navIdle = 'text-slate-600 hover:bg-slate-100 hover:text-indigo-700';
    $avatar = collect(explode(' ', trim($user?->name ?? 'LS')))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white shadow-[0_8px_30px_-20px_rgba(15,23,42,0.35)]">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
        @auth
            <button type="button" id="navigation-toggle" aria-controls="main-navigation" aria-expanded="false" class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-700 transition hover:bg-indigo-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 md:hidden" aria-label="Buka menu navigasi">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        @endauth
        <a href="{{ url('/') }}" class="group flex min-w-0 items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-700 text-sm font-black tracking-wide text-white shadow-lg shadow-indigo-600/20 transition-transform group-hover:-rotate-3 group-hover:scale-105">LS</span>
            <span class="min-w-0"><span class="block truncate text-base font-extrabold leading-tight tracking-tight text-slate-900">LMS Sekolah</span><span class="mt-0.5 block truncate text-xs font-medium text-slate-500">Sistem informasi akademik</span></span>
        </a>
        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @auth
                <div class="hidden items-center gap-2.5 rounded-2xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 sm:flex">
                    <span class="grid size-9 place-items-center rounded-xl bg-indigo-50 text-xs font-bold uppercase text-indigo-700">{{ $avatar }}</span>
                    <span class="max-w-40"><span class="block truncate text-xs font-bold text-slate-800">{{ $user->name }}</span><span class="mt-0.5 block truncate text-[11px] font-medium text-slate-500">{{ $roleLabel[$user->role] ?? $user->role }}</span></span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-400">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Masuk</a>
            @endauth
        </div>
    </div>
    @auth
        <div id="navigation-backdrop" class="fixed inset-0 z-40 hidden bg-slate-950/40 backdrop-blur-[2px] md:hidden"></div>
        <nav id="main-navigation" aria-label="Navigasi utama" class="fixed inset-y-0 left-0 z-50 hidden w-72 max-w-[85vw] overflow-y-auto overscroll-y-contain border-r border-slate-200 bg-white p-4 shadow-2xl shadow-slate-900/10 md:inset-y-auto md:top-[76px] md:z-20 md:flex md:h-[calc(100vh-76px)] md:w-64 md:max-w-none md:flex-col md:shadow-none">
            <div class="mb-5 flex items-center justify-between px-2 pt-1 md:mb-3">
                <div><p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-indigo-500">Navigasi</p><p class="mt-1 text-sm font-bold text-slate-800">Menu utama</p></div>
                <button type="button" id="navigation-close" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="Tutup menu"><svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg></button>
            </div>
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 sm:hidden">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-indigo-100 text-xs font-bold uppercase text-indigo-700">{{ $avatar }}</span>
                <span class="min-w-0"><span class="block truncate text-sm font-bold text-slate-800">{{ $user->name }}</span><span class="mt-0.5 block truncate text-xs text-slate-500">{{ $roleLabel[$user->role] ?? $user->role }}</span></span>
            </div>
            <div class="flex flex-col gap-1 pb-3">
                @if($user->hasRole('admin'))
                    <a href="{{ route('home') }}" @class([$navClass, request()->routeIs('home') ? $navActive : $navIdle]) @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                @elseif($user->hasRole('kepsek'))
                    <a href="{{ route('kpi.index') }}" @class([$navClass, request()->routeIs('kpi.*') ? $navActive : $navIdle]) @if(request()->routeIs('kpi.*')) aria-current="page" @endif>KPI Siswa</a>
                @elseif($user->hasRole('guru'))
                    <a href="{{ route('guru.home') }}" @class([$navClass, request()->routeIs('guru.*') ? $navActive : $navIdle]) @if(request()->routeIs('guru.*')) aria-current="page" @endif>Beranda Guru</a>
                @elseif($user->hasRole('orangtua'))
                    <a href="{{ route('guardian.index') }}" @class([$navClass, request()->routeIs('guardian.*') ? $navActive : $navIdle]) @if(request()->routeIs('guardian.*')) aria-current="page" @endif>Anak Saya</a>
                @elseif($user->hasRole('siswa'))
                    <a href="{{ route('student.home') }}" @class([$navClass, request()->routeIs('student.*') ? $navActive : $navIdle]) @if(request()->routeIs('student.*')) aria-current="page" @endif>Rapor Saya</a>
                @endif
                @if($user->hasAnyRole('admin', 'kepsek'))
                    @can('master.view')
                        <a href="{{ route('master-data.index') }}" @class([$navClass, request()->routeIs('master-data.*') ? $navActive : $navIdle]) @if(request()->routeIs('master-data.*')) aria-current="page" @endif>Data Master</a>
                        <a href="{{ route('semesters.index') }}" @class([$navClass, request()->routeIs('semesters.*') ? $navActive : $navIdle]) @if(request()->routeIs('semesters.*')) aria-current="page" @endif>Semester</a>
                    @endcan
                @endif
                @if($user->hasAnyRole('admin', 'kepsek', 'guru'))
                    <a href="{{ route('grades.index') }}" @class([$navClass, request()->routeIs('grades.*') ? $navActive : $navIdle]) @if(request()->routeIs('grades.*')) aria-current="page" @endif>Nilai</a>
                    <a href="{{ route('attendance.index') }}" @class([$navClass, request()->routeIs('attendance.*') ? $navActive : $navIdle]) @if(request()->routeIs('attendance.*')) aria-current="page" @endif>Absensi</a>
                @endif
                @can('behavior.view')<a href="{{ route('behavior.index') }}" @class([$navClass, request()->routeIs('behavior.*') ? $navActive : $navIdle]) @if(request()->routeIs('behavior.*')) aria-current="page" @endif>Perilaku</a>@endcan
                <a href="{{ route('learning-resources.materials') }}" @class([$navClass, request()->routeIs('learning-resources.materials*') ? $navActive : $navIdle]) @if(request()->routeIs('learning-resources.materials*')) aria-current="page" @endif>Materi</a>
                @can('audit.view')<a href="{{ route('audit.index') }}" @class([$navClass, request()->routeIs('audit.*') ? $navActive : $navIdle]) @if(request()->routeIs('audit.*')) aria-current="page" @endif>Riwayat</a>@endcan
                @can('settings.manage')<a href="{{ route('settings.index') }}" @class([$navClass, request()->routeIs('settings.*') ? $navActive : $navIdle]) @if(request()->routeIs('settings.*')) aria-current="page" @endif>Pengaturan</a>@endcan
                @if(!$user->hasRole('orangtua'))
                    <a href="{{ route('learning-resources.library') }}" @class([$navClass, request()->routeIs('learning-resources.library*') ? $navActive : $navIdle]) @if(request()->routeIs('learning-resources.library*')) aria-current="page" @endif>Library</a>
                @endif
            </div>
        </nav>
        <script>
            const navigationToggle = document.getElementById('navigation-toggle');
            const mainNavigation = document.getElementById('main-navigation');
            const navigationBackdrop = document.getElementById('navigation-backdrop');
            const navigationClose = document.getElementById('navigation-close');

            const closeNavigation = () => {
                if (!window.matchMedia('(max-width: 767px)').matches) return;
                mainNavigation.classList.add('hidden');
                navigationBackdrop.classList.add('hidden');
                navigationToggle?.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');
            };

            navigationToggle?.addEventListener('click', () => {
                const isExpanded = navigationToggle.getAttribute('aria-expanded') === 'true';
                navigationToggle.setAttribute('aria-expanded', String(!isExpanded));
                mainNavigation.classList.toggle('hidden', isExpanded);
                navigationBackdrop.classList.toggle('hidden', isExpanded);
                document.body.classList.toggle('overflow-hidden', !isExpanded);
            });

            navigationClose?.addEventListener('click', closeNavigation);
            navigationBackdrop?.addEventListener('click', closeNavigation);
            mainNavigation?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeNavigation));
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeNavigation(); });
        </script>
    @endauth
</header>

<main @class(['mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8', 'md:ml-64 md:w-[calc(100%-16rem)] md:max-w-none' => auth()->check()])>
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

