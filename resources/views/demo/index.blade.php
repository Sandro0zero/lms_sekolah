@extends('layouts.app')

@section('title', 'Pilih pengguna demo')

@section('content')
    <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 p-4 text-sm">
        Halaman ini hanya untuk pengembangan dan demo (aktif jika <code>APP_ENV=local</code>).
        Pilih pengguna untuk melihat aplikasi dari sudut pandang perannya. Semua akun berpassword <code>password</code>.
    </div>

    @auth
        <form method="POST" action="{{ route('demo.logout') }}">
            @csrf
            <button type="submit" class="rounded border bg-white px-3 py-1.5 text-sm hover:bg-slate-100">Keluar dari {{ auth()->user()->name }}</button>
        </form>
    @endauth

    @foreach($roles as $role => $label)
        @php $list = $users->get($role, collect()); @endphp
        @continue($list->isEmpty())

        <details class="bg-white border rounded-xl" {{ in_array($role, ['admin', 'kepsek', 'guru']) ? 'open' : '' }}>
            <summary class="cursor-pointer px-4 py-3 font-medium">
                {{ $label }} <span class="text-slate-500 text-sm">({{ $list->count() }})</span>
            </summary>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2 p-4 border-t">
                @foreach($list as $u)
                    <form method="POST" action="{{ route('demo.switch', $u) }}">
                        @csrf
                        <button type="submit" class="w-full text-left rounded-lg border px-3 py-2 hover:bg-indigo-50">
                            <span class="block font-medium">{{ $u->name }}</span>
                            <span class="block text-xs text-slate-500">
                                {{ $u->username }}@if($hints->has($u->id)) · {{ $hints[$u->id] }}@endif
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </details>
    @endforeach
@endsection