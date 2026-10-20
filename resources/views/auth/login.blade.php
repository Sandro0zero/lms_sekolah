@extends('layouts.app')

@section('title', 'Masuk · LMS Sekolah')

@section('content')
<div class="mx-auto max-w-md">
    <div class="mb-6 text-center">
        <p class="text-sm font-medium text-indigo-700">LMS Sekolah</p>
        <h1 class="mt-2 text-2xl font-semibold">Masuk ke akun</h1>
        <p class="mt-2 text-sm text-slate-500">Gunakan akun sekolah yang diberikan administrator.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
        @csrf

        <div>
            <label for="login" class="mb-1 block text-sm font-medium">Nama pengguna atau email</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required autofocus
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
            @error('login')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-medium">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
            @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <label for="remember" class="flex items-center gap-2 text-sm text-slate-600">
            <input id="remember" name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            Ingat saya di perangkat ini
        </label>

        @if($errors->has('auth'))
            <p role="alert" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800">{{ $errors->first('auth') }}</p>
        @endif

        <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Masuk
        </button>
    </form>

    @if(app()->environment('local'))
        <p class="mt-4 text-center text-sm text-slate-500">Untuk demo lokal, <a href="{{ route('demo.index') }}" class="font-medium text-indigo-700 hover:underline">pilih akun demo</a>.</p>
    @endif
</div>
@endsection
