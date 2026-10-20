@extends('layouts.app')

@section('title', 'Kelola Siswa · LMS Sekolah')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><a href="{{ route('master-data.section', ['section' => 'siswa', 'tab' => 'data']) }}" class="text-sm font-semibold text-indigo-700 hover:underline">← Kembali ke Data Siswa</a><h1 class="mt-2 text-2xl font-bold tracking-tight">Kelola Data Siswa</h1><p class="mt-1 text-sm text-slate-500">Perbarui profil {{ $student->name }}, foto, kelas, dan data orang tua/wali.</p></div>
        @if($student->photo_path)<img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto {{ $student->name }}" class="size-16 rounded-2xl border object-cover">@endif
    </div>

    <form method="POST" action="{{ route('master-data.students.update', $student) }}" enctype="multipart/form-data" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        @csrf @method('PUT')
        <section>
            <h2 class="font-semibold text-slate-900">Identitas siswa</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="text-sm font-medium">NIS<input name="nis" value="{{ old('nis', $student->nis) }}" required maxlength="30" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">NISN<input name="nisn" value="{{ old('nisn', $student->nisn) }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">NIK<input name="nik" value="{{ old('nik', $student->nik) }}" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium sm:col-span-2">Nama lengkap<input name="name" value="{{ old('name', $student->name) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Jenis kelamin<select name="gender" required class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"><option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki</option><option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan</option></select></label>
                <label class="text-sm font-medium">Tempat lahir<input name="birth_place" value="{{ old('birth_place', $student->birth_place) }}" maxlength="100" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Tanggal lahir<input name="birth_date" type="date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Agama<input name="religion" value="{{ old('religion', $student->religion) }}" maxlength="50" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Nomor HP siswa (opsional)<input name="phone" value="{{ old('phone', $student->phone) }}" maxlength="20" class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Tanggal terdaftar<input name="registration_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('registration_date', $student->registration_date?->format('Y-m-d')) }}" required class="mt-1.5 w-full rounded-lg border px-3 py-2"></label>
                <label class="text-sm font-medium">Status<select name="status" required class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2">@foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $student->status) === $value)>{{ $label }}</option>@endforeach</select></label>
                @php($enrollment = $student->enrollments->sortByDesc('academic_year')->first())
                <label class="text-sm font-medium">Kelas<select name="class_id" class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"><option value="">Pertahankan penempatan saat ini</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('class_id', $enrollment?->class_id) == $class->id)>{{ $class->name }} · {{ $class->academic_year }}</option>@endforeach</select></label>
                <label class="text-sm font-medium sm:col-span-2">Ganti foto (JPG/PNG/WebP, maks. 2 MB)<input name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2"></label>
            </div>
        </section>

        @php($parentsByRelationship = $student->guardians->keyBy(fn ($guardian) => $guardian->pivot->relationship))
        <section class="border-t pt-5">
            <h2 class="font-semibold text-slate-900">Orang tua / wali</h2><p class="mt-1 text-xs text-slate-500">Ayah atau Ibu wajib diisi minimal salah satu. Wali opsional.</p>
            <div class="mt-4 grid gap-4 md:grid-cols-3">
                @foreach(['father' => ['Ayah', 'ayah'], 'mother' => ['Ibu', 'ibu'], 'guardian' => ['Wali (opsional)', 'wali']] as $prefix => [$label, $relationship])
                    @php($parent = $parentsByRelationship->get($relationship))
                    <fieldset class="space-y-3 rounded-xl border p-4"><legend class="px-1 text-sm font-semibold">{{ $label }}</legend>
                        <label class="block text-xs font-medium">Nama<input name="{{ $prefix }}_name" value="{{ old($prefix.'_name', $parent?->name) }}" class="mt-1 w-full rounded-lg border px-3 py-2"></label>
                        <label class="block text-xs font-medium">Nomor HP<input name="{{ $prefix }}_phone" value="{{ old($prefix.'_phone', $parent?->phone) }}" class="mt-1 w-full rounded-lg border px-3 py-2"></label>
                        <label class="block text-xs font-medium">Pekerjaan<input name="{{ $prefix }}_occupation" value="{{ old($prefix.'_occupation', $parent?->occupation) }}" maxlength="100" class="mt-1 w-full rounded-lg border px-3 py-2"></label>
                    </fieldset>
                @endforeach
            </div>
        </section>

        <section class="flex flex-wrap items-center justify-between gap-3 border-t pt-5">
            <p class="text-xs text-slate-500">Email login dibuat otomatis: {{ $student->user?->email ?? 'belum tersedia' }}. Kata sandi awal mengikuti tanggal lahir (YYYY-MM-DD).</p>
            <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Simpan perubahan</button>
        </section>
    </form>

    <section class="rounded-2xl border border-rose-200 bg-white p-5">
        <h2 class="font-semibold text-rose-800">Arsipkan siswa</h2><p class="mt-1 text-sm text-slate-600">Akun masuk siswa akan dinonaktifkan dan riwayat akademik tetap disimpan.</p>
        <form method="POST" action="{{ route('master-data.students.destroy', $student) }}" class="mt-3" onsubmit="return confirm('Arsipkan siswa ini? Akun masuk akan dinonaktifkan, riwayat tetap tersimpan.')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Arsipkan siswa</button></form>
    </section>
</div>
@endsection
