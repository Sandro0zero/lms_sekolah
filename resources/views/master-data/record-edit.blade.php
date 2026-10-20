@extends('layouts.app')

@php($titles = ['kelas' => 'Kelola Kelas', 'guru' => 'Kelola Guru', 'mapel' => 'Kelola Mata Pelajaran', 'penugasan' => 'Kelola Penugasan'])
@section('title', $titles[$type].' · LMS Sekolah')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <div><a href="{{ route('master-data.section', ['section' => $type, 'tab' => 'data']) }}" class="text-sm font-semibold text-indigo-700 hover:underline">← Kembali ke Data {{ $sectionLabels[$type] ?? ['kelas' => 'Kelas', 'guru' => 'Guru', 'mapel' => 'Mata Pelajaran', 'penugasan' => 'Penugasan'][$type] }}</a><h1 class="mt-2 text-2xl font-bold tracking-tight">{{ $titles[$type] }}</h1></div>

    @if($type === 'kelas')
        <form method="POST" action="{{ route('master-data.classes.update', $record) }}" class="space-y-4 rounded-2xl border bg-white p-5 shadow-sm">@csrf @method('PUT')
            <label class="block text-sm font-medium">Nama kelas<input name="name" value="{{ old('name', $record->name) }}" maxlength="30" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Tingkat kelas<input name="grade_level" type="number" min="1" max="12" value="{{ old('grade_level', $record->grade_level) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Wali kelas<select name="homeroom_teacher_id" class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"><option value="">Belum ditentukan</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $record->homeroom_teacher_id) == $teacher->id)>{{ $teacher->name }}</option>@endforeach</select></label>
            <p class="text-xs text-slate-500">Tahun ajaran: {{ $record->academic_year }}</p><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button>
        </form>
        @if($record->enrollments()->count() === 0 && $record->assignments()->count() === 0)
            <form method="POST" action="{{ route('master-data.classes.destroy', $record) }}" onsubmit="return confirm('Hapus kelas kosong ini?')" class="rounded-xl border border-rose-200 bg-white p-4">@csrf @method('DELETE')<button class="text-sm font-semibold text-rose-700">Hapus kelas kosong</button></form>
        @endif
    @elseif($type === 'mapel')
        <form method="POST" action="{{ route('master-data.subjects.update', $record) }}" class="space-y-4 rounded-2xl border bg-white p-5 shadow-sm">@csrf @method('PUT')
            <label class="block text-sm font-medium">Kode (opsional)<input name="code" value="{{ old('code', $record->code) }}" maxlength="20" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Nama mata pelajaran<input name="name" value="{{ old('name', $record->name) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Status<select name="is_active" class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"><option value="1" @selected($record->is_active)>Aktif</option><option value="0" @selected(! $record->is_active)>Nonaktif</option></select></label><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button>
        </form>
    @elseif($type === 'guru')
        <form method="POST" action="{{ route('master-data.teachers.update', $record) }}" class="space-y-4 rounded-2xl border bg-white p-5 shadow-sm">@csrf @method('PUT')
            <label class="block text-sm font-medium">Nama guru<input name="name" value="{{ old('name', $record->name) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Username<input name="username" value="{{ old('username', $record->user?->username) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <label class="block text-sm font-medium">Email (opsional)<input name="email" type="email" value="{{ old('email', $record->user?->email) }}" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
            <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-medium">NIP<input name="nip" value="{{ old('nip', $record->nip) }}" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label><label class="block text-sm font-medium">Nomor HP<input name="phone" value="{{ old('phone', $record->phone) }}" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label></div>
            <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-medium">Kata sandi baru (opsional)<input name="password" type="password" minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label><label class="block text-sm font-medium">Ulangi kata sandi<input name="password_confirmation" type="password" minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label></div>
            <label class="block text-sm font-medium">Status akun<select name="is_active" class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"><option value="1" @selected($record->is_active)>Aktif</option><option value="0" @selected(! $record->is_active)>Nonaktif</option></select></label><button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">Simpan perubahan</button>
        </form>
    @else
        <section class="rounded-2xl border bg-white p-5 shadow-sm"><h2 class="font-semibold">Detail penugasan</h2><dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2"><div><dt class="text-slate-500">Guru</dt><dd class="font-medium">{{ $record->teacher->name }}</dd></div><div><dt class="text-slate-500">Mata pelajaran</dt><dd class="font-medium">{{ $record->subject->name }}</dd></div><div><dt class="text-slate-500">Kelas</dt><dd class="font-medium">{{ $record->schoolClass->name }}</dd></div><div><dt class="text-slate-500">Semester</dt><dd class="font-medium">{{ $record->semester->label() }}</dd></div></dl></section>
        <form method="POST" action="{{ route('master-data.assignments.destroy', $record) }}" onsubmit="return confirm('Hapus penugasan ini? Nilai dan absensi yang sudah tercatat tetap tersimpan.')" class="rounded-xl border border-rose-200 bg-white p-4">@csrf @method('DELETE')<button class="text-sm font-semibold text-rose-700">Hapus penugasan</button></form>
    @endif
</div>
@endsection
