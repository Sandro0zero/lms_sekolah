@extends('layouts.app')

@section('title', 'Riwayat Perubahan · LMS Sekolah')

@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Riwayat Perubahan</h1>
    <p class="mt-1 text-sm text-slate-600">Lihat siapa yang mengubah data, kapan perubahan terjadi, dan alasan koreksi jika dicatat.</p>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <div class="rounded-xl border bg-white p-4"><p class="text-sm text-slate-500">Total perubahan tercatat</p><p class="mt-1 text-2xl font-semibold">{{ number_format($totalLogs) }}</p></div>
    <div class="rounded-xl border bg-white p-4"><p class="text-sm text-slate-500">Perubahan hari ini</p><p class="mt-1 text-2xl font-semibold">{{ number_format($todayLogs) }}</p></div>
</div>

<section class="rounded-xl border bg-white p-5">
    <form method="GET" action="{{ route('audit.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label for="audit-search" class="mb-1 block text-sm font-medium">Cari pelaku, jenis data, atau ID</label>
            <input id="audit-search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div>
            <label for="audit-event" class="mb-1 block text-sm font-medium">Jenis perubahan</label>
            <select id="audit-event" name="event" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                <option value="">Semua jenis</option>
                @foreach($events as $event)<option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>{{ ucfirst(str_replace(['.', '_'], ' ', $event)) }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="audit-user" class="mb-1 block text-sm font-medium">Pelaku</label>
            <select id="audit-user" name="user_id" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                <option value="">Semua pelaku</option>
                @foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }} · {{ '@'.$user->username }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="audit-from" class="mb-1 block text-sm font-medium">Dari tanggal</label>
            <input id="audit-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div>
            <label for="audit-to" class="mb-1 block text-sm font-medium">Sampai tanggal</label>
            <input id="audit-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Terapkan filter</button>
            <a href="{{ route('audit.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-slate-50">Reset</a>
        </div>
    </form>
</section>

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500"><tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">Pelaku</th><th class="px-4 py-3">Perubahan</th><th class="px-4 py-3">Data</th><th class="px-4 py-3">Rincian</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-t align-top">
                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $log->created_at?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? 'Akun tidak tersedia' }}</td>
                        <td class="px-4 py-3 font-medium">{{ ucfirst(str_replace(['.', '_'], ' ', $log->event)) }}</td>
                        <td class="px-4 py-3"><span class="font-medium">{{ class_basename($log->auditable_type) }}</span><span class="block text-xs text-slate-500">ID {{ $log->auditable_id }}</span></td>
                        <td class="min-w-56 px-4 py-3">
                            <details>
                                <summary class="cursor-pointer text-indigo-700 hover:underline">Lihat detail</summary>
                                <div class="mt-3 space-y-3">
                                    @if($log->reason)<div><p class="text-xs font-semibold text-slate-600">Alasan</p><p class="mt-1 text-sm">{{ $log->reason }}</p></div>@endif
                                    @if($log->ip_address)<div><p class="text-xs font-semibold text-slate-600">Alamat jaringan</p><p class="mt-1 text-xs">{{ $log->ip_address }}</p></div>@endif
                                    <div class="grid gap-3 xl:grid-cols-2">
                                        <div><p class="text-xs font-semibold text-slate-600">Sebelum</p><pre class="mt-1 max-h-64 overflow-auto rounded bg-slate-50 p-2 text-xs">{{ $log->old_values ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Tidak ada data sebelumnya' }}</pre></div>
                                        <div><p class="text-xs font-semibold text-slate-600">Sesudah</p><pre class="mt-1 max-h-64 overflow-auto rounded bg-slate-50 p-2 text-xs">{{ $log->new_values ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'Tidak ada data sesudahnya' }}</pre></div>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada riwayat perubahan yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="border-t px-4 py-3">{{ $logs->links() }}</div>@endif
</section>
@endsection
