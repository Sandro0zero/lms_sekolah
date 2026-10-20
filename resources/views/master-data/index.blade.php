@extends('layouts.app')

@section('title', ($section ? $sectionLabels[$section] : 'Data Master').' · LMS Sekolah')

@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $section ? $sectionLabels[$section] : 'Data Master' }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $section ? 'Kelola '.strtolower($sectionLabels[$section]).'.' : 'Pilih jenis data yang ingin dikelola.' }}</p>
</div>

@if(! $semester)
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        Belum ada semester aktif. Buat dan aktifkan semester terlebih dahulu di <a class="font-semibold underline" href="{{ route('semesters.index') }}">Pengelolaan Semester</a>.
    </div>
@endif

@if(! $section)
    @php($masterLinks = [
        ['key' => 'siswa', 'title' => 'Siswa', 'description' => 'Profil, akun masuk, status, dan daftar siswa.'],
        ['key' => 'kelas', 'title' => 'Kelas', 'description' => 'Tingkat, tahun ajaran, dan wali kelas.'],
        ['key' => 'guru', 'title' => 'Guru', 'description' => 'Profil, akun guru, dan status akun.'],
        ['key' => 'mapel', 'title' => 'Mata Pelajaran', 'description' => 'Kode, nama, dan status mata pelajaran.'],
        ['key' => 'orang-tua', 'title' => 'Orang Tua/Wali', 'description' => 'Akun wali dan hubungan dengan siswa.'],
        ['key' => 'penugasan', 'title' => 'Penugasan Guru', 'description' => 'Guru, kelas, dan mata pelajaran semester aktif.'],
    ])
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($masterLinks as $link)
            <a href="{{ route('master-data.section', $link['key']) }}" class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div><h2 class="font-semibold text-slate-900 group-hover:text-indigo-700">{{ $link['title'] }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">{{ $link['description'] }}</p></div>
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-indigo-50 text-lg font-semibold text-indigo-700 transition group-hover:bg-indigo-600 group-hover:text-white" aria-hidden="true">›</span>
                </div>
            </a>
        @endforeach
    </section>
@else
    <nav aria-label="Jenis data master" class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1">
        <a href="{{ route('master-data.index') }}" class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:border-indigo-300 hover:text-indigo-700">Semua data</a>
        @foreach($sectionLabels as $key => $label)
            <a href="{{ route('master-data.section', $key) }}" @class(['shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold transition', $section === $key ? 'bg-indigo-600 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-700']) @if($section === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    <form method="GET" action="{{ request()->url() }}" role="search" class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="min-w-56 flex-1">
            <label for="master_search" class="mb-1 block text-xs font-semibold text-slate-600">Cari {{ strtolower($sectionLabels[$section]) }}</label>
            <input id="master_search" name="q" type="search" value="{{ $search }}" maxlength="100" placeholder="Masukkan nama atau nomor terkait…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
        </div>
        @if($section === 'siswa')
            <div>
                <label for="entry_year" class="mb-1 block text-xs font-semibold text-slate-600">Tahun masuk</label>
                <select id="entry_year" name="entry_year" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                    <option value="">Semua tahun</option>
                    @foreach($registrationYears as $year)<option value="{{ $year }}" @selected($entryYear === (int) $year)>{{ $year }}</option>@endforeach
                </select>
            </div>
        @endif
        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Cari</button>
        @if($search !== '' || $entryYear)<a href="{{ route('master-data.section', $section) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Hapus filter</a>@endif
    </form>
    @can('master.manage')
        <div class="mb-5 flex items-center justify-between gap-4 rounded-xl border border-indigo-100 bg-indigo-50/70 px-4 py-3">
            <div><p class="text-sm font-semibold text-indigo-950">Tambah {{ strtolower($sectionLabels[$section]) }}</p><p class="mt-0.5 text-xs text-indigo-800/80">Isi formulir berikut untuk membuat data baru.</p></div>
            <a href="#form-tambah" class="shrink-0 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">+ Tambah</a>
        </div>
    @endcan
@endif

@if($section)
@can('master.manage')
    <div id="form-tambah" class="grid scroll-mt-36 gap-5 lg:grid-cols-2 xl:grid-cols-3">
        @if($section === 'kelas')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Tambah kelas</h2>
            @if($academicYears->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Tambahkan semester sebelum membuat kelas.</p>
            @else
                <form method="POST" action="{{ route('master-data.classes.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="class_name" class="mb-1 block text-sm font-medium">Nama kelas</label>
                        <input id="class_name" name="name" value="{{ old('name') }}" placeholder="7A" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="grade_level" class="mb-1 block text-sm font-medium">Tingkat</label>
                        <input id="grade_level" name="grade_level" type="number" min="1" max="12" value="{{ old('grade_level') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('grade_level')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="class_academic_year" class="mb-1 block text-sm font-medium">Tahun ajaran</label>
                        <select id="class_academic_year" name="academic_year" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            @foreach($academicYears as $year)<option value="{{ $year }}" @selected(old('academic_year', $semester?->academic_year) === $year)>{{ $year }}</option>@endforeach
                        </select>
                        @error('academic_year')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="homeroom_teacher_id" class="mb-1 block text-sm font-medium">Wali kelas (opsional)</label>
                        <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <option value="">Belum ditentukan</option>
                            @foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id') == $teacher->id)>{{ $teacher->name }}</option>@endforeach
                        </select>
                        @error('homeroom_teacher_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan kelas</button>
                </form>
            @endif
        </section>
        @endif

        @if($section === 'siswa')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Tambah siswa</h2>
            @if($classes->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Buat kelas terlebih dahulu sebelum mendaftarkan siswa.</p>
            @else
                <form method="POST" action="{{ route('master-data.students.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="student_nis" class="mb-1 block text-sm font-medium">NIS</label>
                        <input id="student_nis" name="nis" value="{{ old('nis') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('nis')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_nisn" class="mb-1 block text-sm font-medium">NISN (opsional)</label>
                        <input id="student_nisn" name="nisn" value="{{ old('nisn') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('nisn')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_name" class="mb-1 block text-sm font-medium">Nama siswa</label>
                        <input id="student_name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div>
                            <label for="gender" class="mb-1 block text-sm font-medium">Jenis kelamin</label>
                            <select id="gender" name="gender" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                                <option value="">Pilih</option><option value="L" @selected(old('gender') === 'L')>Laki-laki</option><option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                            </select>
                            @error('gender')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="birth_date" class="mb-1 block text-sm font-medium">Tanggal lahir</label>
                            <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('birth_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_registration_date" class="mb-1 block text-sm font-medium">Tanggal terdaftar</label>
                            <input id="student_registration_date" name="registration_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('registration_date', now()->toDateString()) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('registration_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="student_class_id" class="mb-1 block text-sm font-medium">Kelas</label>
                        <select id="student_class_id" name="class_id" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <option value="">Belum ditempatkan — pilih wali kelas nanti</option>
                            @foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>Kelas {{ $class->name }} · {{ $class->academic_year }}</option>@endforeach
                        </select>
                        @error('class_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <details class="rounded-lg border p-3">
                        <summary class="cursor-pointer text-sm font-medium">Buat akun login siswa (opsional)</summary>
                        <p class="mt-1 text-xs text-slate-500">Isi nama pengguna dan kata sandi jika siswa akan masuk ke aplikasi.</p>
                        <div class="mt-3 space-y-2">
                            <input name="username" value="{{ old('username') }}" placeholder="Nama pengguna" autocomplete="off" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Nama pengguna siswa">
                            <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (opsional)" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Email siswa">
                            <input name="password" type="password" autocomplete="new-password" placeholder="Kata sandi awal, minimal 12 karakter" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Kata sandi siswa">
                            <input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Ulangi kata sandi siswa">
                            @error('username')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                            @error('password')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </details>
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan siswa</button>
                </form>
            @endif
        </section>
        @endif

        @if($section === 'mapel')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Tambah mata pelajaran</h2>
            <form method="POST" action="{{ route('master-data.subjects.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="subject_code" class="mb-1 block text-sm font-medium">Kode (opsional)</label>
                    <input id="subject_code" name="code" value="{{ old('code') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('code')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="subject_name" class="mb-1 block text-sm font-medium">Nama mata pelajaran</label>
                    <input id="subject_name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan mata pelajaran</button>
            </form>
        </section>
        @endif

        @if($section === 'guru')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Buat akun guru</h2>
            <p class="mt-1 text-xs text-slate-500">Akun dapat langsung digunakan untuk masuk. Kata sandi minimal 12 karakter.</p>
            <form method="POST" action="{{ route('master-data.teachers.store') }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label for="teacher_name" class="mb-1 block text-sm font-medium">Nama guru</label>
                    <input id="teacher_name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="teacher_username" class="mb-1 block text-sm font-medium">Nama pengguna</label>
                    <input id="teacher_username" name="username" value="{{ old('username') }}" autocomplete="off" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('username')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="teacher_email" class="mb-1 block text-sm font-medium">Email (opsional)</label>
                        <input id="teacher_email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="teacher_nip" class="mb-1 block text-sm font-medium">NIP (opsional)</label>
                        <input id="teacher_nip" name="nip" value="{{ old('nip') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('nip')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="teacher_phone" class="mb-1 block text-sm font-medium">Nomor telepon (opsional)</label>
                    <input id="teacher_phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border px-3 py-2 text-sm">
                    @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="teacher_password" class="mb-1 block text-sm font-medium">Kata sandi awal</label>
                        <input id="teacher_password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="teacher_password_confirmation" class="mb-1 block text-sm font-medium">Ulangi kata sandi</label>
                        <input id="teacher_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border px-3 py-2 text-sm">
                    </div>
                </div>
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat akun guru</button>
            </form>
        </section>
        @endif

        @if($section === 'orang-tua')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Buat akun orang tua/wali</h2>
            <p class="mt-1 text-xs text-slate-500">Satu akun dapat terhubung ke beberapa anak. Kata sandi minimal 12 karakter.</p>
            @if($allStudents->isEmpty())
                <p class="mt-4 text-sm text-slate-500">Tambahkan siswa terlebih dahulu.</p>
            @else
                <form method="POST" action="{{ route('master-data.guardians.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input name="name" value="{{ old('name') }}" placeholder="Nama orang tua/wali" required class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Nama orang tua/wali">
                    <input name="username" value="{{ old('username') }}" placeholder="Nama pengguna" required autocomplete="off" class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Nama pengguna">
                    <input name="email" type="email" value="{{ old('email') }}" placeholder="Email (opsional)" class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Email">
                    <input name="phone" value="{{ old('phone') }}" placeholder="Nomor telepon (opsional)" class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Nomor telepon">
                    <div class="grid grid-cols-2 gap-2">
                        <input name="password" type="password" autocomplete="new-password" placeholder="Kata sandi awal" required class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Kata sandi awal">
                        <input name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi" required class="w-full rounded-lg border px-3 py-2 text-sm" aria-label="Ulangi kata sandi">
                    </div>
                    <label class="block text-sm font-medium">Hubungan dengan anak</label>
                    <select name="relationship" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                        <option value="ayah">Ayah</option><option value="ibu">Ibu</option><option value="wali">Wali</option>
                    </select>
                    <div class="max-h-36 space-y-1 overflow-y-auto rounded-lg border p-2">
                        @foreach($allStudents as $child)
                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="student_ids[]" value="{{ $child->id }}" @checked(in_array($child->id, old('student_ids', [])))> {{ $child->name }} · NIS {{ $child->nis }}</label>
                        @endforeach
                    </div>
                    @error('student_ids')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                    @error('password')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Buat akun orang tua/wali</button>
                </form>
            @endif
        </section>
        @endif

        @if($section === 'penugasan')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Tugaskan guru</h2>
            <p class="mt-1 text-xs text-slate-500">Penugasan membatasi kelas dan mata pelajaran yang muncul bagi guru.</p>
            @if(! $semester)
                <p class="mt-4 text-sm text-slate-500">Aktifkan semester sebelum membuat penugasan.</p>
            @elseif($teachers->isEmpty() || $classes->where('academic_year', $semester->academic_year)->isEmpty() || $subjects->where('is_active', true)->isEmpty())
                <p class="mt-4 text-sm text-slate-500">Siapkan akun guru, kelas, dan mata pelajaran terlebih dahulu.</p>
            @else
                <form method="POST" action="{{ route('master-data.assignments.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="assignment_teacher" class="mb-1 block text-sm font-medium">Guru</label>
                        <select id="assignment_teacher" name="teacher_id" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <option value="">Pilih guru</option>
                            @foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }}</option>@endforeach
                        </select>
                        @error('teacher_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="assignment_class" class="mb-1 block text-sm font-medium">Kelas</label>
                        <select id="assignment_class" name="class_id" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <option value="">Pilih kelas</option>
                            @foreach($classes->where('academic_year', $semester->academic_year) as $class)<option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>@endforeach
                        </select>
                        @error('class_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="assignment_subject" class="mb-1 block text-sm font-medium">Mata pelajaran</label>
                        <select id="assignment_subject" name="subject_id" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <option value="">Pilih mata pelajaran</option>
                            @foreach($subjects->where('is_active', true) as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>@endforeach
                        </select>
                        @error('subject_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan penugasan</button>
                </form>
            @endif
        </section>
        @endif
    </div>
@endcan

<div class="grid gap-5 {{ $section === 'siswa' ? 'lg:grid-cols-2 xl:grid-cols-3' : 'lg:grid-cols-2' }}">
    @if($section === 'kelas')
    <section class="overflow-hidden rounded-xl border bg-white">
        <h2 class="border-b px-5 py-4 font-semibold">Kelas <span class="text-sm font-normal text-slate-500">({{ $classes->count() }})</span></h2>
        <div class="divide-y">
            @forelse($classes as $class)
                <div class="px-5 py-3 text-sm">
                    <p class="font-medium">{{ $class->name }} · tingkat {{ $class->grade_level }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $class->academic_year }} · Wali kelas: {{ $class->homeroomTeacher?->name ?? 'Belum ditentukan' }}</p>
                    @can('master.manage')
                        <details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-indigo-700">Ubah kelas</summary>
                            <form method="POST" action="{{ route('master-data.classes.update', $class) }}" class="mt-2 space-y-2">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $class->name }}" required maxlength="30" class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama kelas">
                                <input name="grade_level" type="number" min="1" max="12" value="{{ $class->grade_level }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Tingkat kelas">
                                <select name="homeroom_teacher_id" class="w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Wali kelas">
                                    <option value="">Belum ditentukan</option>
                                    @foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($class->homeroom_teacher_id == $teacher->id)>{{ $teacher->name }}</option>@endforeach
                                </select>
                                <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white">Simpan perubahan</button>
                            </form>
                        </details>
                        @if($class->enrollments_count === 0 && $class->assignments_count === 0)
                            <form method="POST" action="{{ route('master-data.classes.destroy', $class) }}" class="mt-2" onsubmit="return confirm('Hapus kelas kosong ini?')">
                                @csrf @method('DELETE')<button class="text-xs text-red-700 underline">Hapus kelas kosong</button>
                            </form>
                        @else
                            <p class="mt-1 text-xs text-slate-400">Kelas berisi data, tidak dapat dihapus.</p>
                        @endif
                    @endcan
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">{{ $search !== '' ? 'Tidak ada kelas yang cocok dengan pencarian.' : 'Belum ada kelas.' }}</p>
            @endforelse
        </div>
    </section>
    @endif

    @if($section === 'siswa')
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="border-b px-5 py-4"><h2 class="font-semibold">Siswa <span class="text-sm font-normal text-slate-500">({{ $students->total() }})</span></h2><p class="mt-1 text-xs text-slate-500">Urut dari tahun dan tanggal terdaftar terbaru.</p></div>
        <div class="divide-y">
            @forelse($students as $student)
                @php($enrollment = $student->enrollments->sortByDesc('academic_year')->first())
                <div class="px-5 py-3 text-sm">
                    <p class="font-medium">{{ $student->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">NIS {{ $student->nis }} · {{ $enrollment?->schoolClass?->name ? 'Kelas '.$enrollment->schoolClass->name : 'Belum memiliki kelas' }} · Terdaftar {{ $student->registration_date?->format('d/m/Y') ?? '—' }} · {{ ucfirst($student->status) }}</p>
                    @can('master.manage')
                        <details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-indigo-700">Ubah data siswa</summary>
                            <form method="POST" action="{{ route('master-data.students.update', $student) }}" class="mt-2 space-y-2">
                                @csrf @method('PUT')
                                <input name="nis" value="{{ $student->nis }}" required maxlength="30" class="w-full rounded border px-2 py-1 text-xs" aria-label="NIS">
                                <input name="nisn" value="{{ $student->nisn }}" maxlength="30" class="w-full rounded border px-2 py-1 text-xs" aria-label="NISN">
                                <input name="name" value="{{ $student->name }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama siswa">
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="gender" required class="rounded border bg-white px-2 py-1 text-xs" aria-label="Jenis kelamin">
                                        <option value="L" @selected($student->gender === 'L')>Laki-laki</option><option value="P" @selected($student->gender === 'P')>Perempuan</option>
                                    </select>
                                    <select name="status" required class="rounded border bg-white px-2 py-1 text-xs" aria-label="Status siswa">
                                        @foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah', 'keluar' => 'Keluar'] as $value => $label)<option value="{{ $value }}" @selected($student->status === $value)>{{ $label }}</option>@endforeach
                                    </select>
                                </div>
                                <input name="birth_date" type="date" value="{{ $student->birth_date?->format('Y-m-d') }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="Tanggal lahir">
                                <label class="block text-xs font-medium text-slate-600">Tanggal terdaftar
                                    <input name="registration_date" type="date" max="{{ now()->toDateString() }}" value="{{ $student->registration_date?->format('Y-m-d') }}" required class="mt-1 w-full rounded border px-2 py-1 text-xs" aria-label="Tanggal terdaftar siswa">
                                </label>
                                <select name="class_id" class="w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Kelas siswa">
                                    <option value="" @selected(! $enrollment)>Pertahankan penempatan saat ini</option>
                                    @foreach($classes as $class)<option value="{{ $class->id }}" @selected($enrollment?->class_id === $class->id)>Kelas {{ $class->name }} · {{ $class->academic_year }}</option>@endforeach
                                </select>
                                <details class="rounded border p-2">
                                    <summary class="cursor-pointer text-xs font-medium">Akun login siswa</summary>
                                    <input name="username" value="{{ $student->user?->username }}" placeholder="Nama pengguna" autocomplete="off" class="mt-2 w-full rounded border px-2 py-1 text-xs" aria-label="Nama pengguna siswa">
                                    <input name="email" type="email" value="{{ $student->user?->email }}" placeholder="Email (opsional)" class="mt-2 w-full rounded border px-2 py-1 text-xs" aria-label="Email siswa">
                                    <label class="mt-2 block text-xs text-slate-600">Kata sandi baru (kosongkan jika tidak diubah)</label>
                                    <input name="password" type="password" autocomplete="new-password" minlength="12" class="mt-1 w-full rounded border px-2 py-1 text-xs" aria-label="Kata sandi baru siswa">
                                    <input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" placeholder="Ulangi kata sandi baru" class="mt-2 w-full rounded border px-2 py-1 text-xs" aria-label="Ulangi kata sandi baru siswa">
                                    <select name="account_active" class="mt-2 w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Status akun login siswa">
                                        <option value="1" @selected($student->user?->is_active)>Akun aktif</option><option value="0" @selected($student->user && ! $student->user->is_active)>Akun nonaktif</option>
                                    </select>
                                </details>
                                <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white">Simpan perubahan</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('master-data.students.destroy', $student) }}" class="mt-2" onsubmit="return confirm('Arsipkan siswa ini? Akun masuknya akan dinonaktifkan, riwayat tetap tersimpan.')">
                            @csrf @method('DELETE')<button class="text-xs text-red-700 underline">Arsipkan siswa</button>
                        </form>
                    @endcan
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">{{ $search !== '' ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada siswa.' }}</p>
            @endforelse
        </div>
        @if($students->hasPages())<div class="border-t px-5 py-3">{{ $students->links() }}</div>@endif
    </section>
    @endif

    @if($section === 'mapel')
    <section class="overflow-hidden rounded-xl border bg-white">
        <h2 class="border-b px-5 py-4 font-semibold">Mata pelajaran <span class="text-sm font-normal text-slate-500">({{ $subjects->count() }})</span></h2>
        <div class="divide-y">
            @forelse($subjects as $subject)
                <div class="px-5 py-3 text-sm">
                    <p class="font-medium">{{ $subject->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $subject->code ?? 'Tanpa kode' }} · {{ $subject->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    @can('master.manage')
                        <details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-indigo-700">Ubah mapel</summary>
                            <form method="POST" action="{{ route('master-data.subjects.update', $subject) }}" class="mt-2 space-y-2">
                                @csrf @method('PUT')
                                <input name="code" value="{{ $subject->code }}" maxlength="20" class="w-full rounded border px-2 py-1 text-xs" aria-label="Kode mapel">
                                <input name="name" value="{{ $subject->name }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama mapel">
                                <select name="is_active" class="w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Status mapel">
                                    <option value="1" @selected($subject->is_active)>Aktif</option><option value="0" @selected(! $subject->is_active)>Nonaktif</option>
                                </select>
                                <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white">Simpan perubahan</button>
                            </form>
                        </details>
                    @endcan
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">{{ $search !== '' ? 'Tidak ada mata pelajaran yang cocok dengan pencarian.' : 'Belum ada mata pelajaran.' }}</p>
            @endforelse
        </div>
    </section>
    @endif

    @if($section === 'guru')
    <section class="overflow-hidden rounded-xl border bg-white">
        <h2 class="border-b px-5 py-4 font-semibold">Guru <span class="text-sm font-normal text-slate-500">({{ $teacherProfiles->count() }})</span></h2>
        <div class="divide-y">
            @forelse($teacherProfiles as $teacher)
                <div class="px-5 py-3 text-sm">
                    <p class="font-medium">{{ $teacher->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $teacher->user?->username ? '@'.$teacher->user->username : 'Belum ada akun masuk' }} · {{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    @if($teacher->assignments->isNotEmpty())
                        <div class="mt-2 space-y-1 text-xs text-indigo-700">
                            @foreach($teacher->assignments as $assignment)
                                <div class="flex items-center justify-between gap-2">
                                    <span>{{ $assignment->subject->name }} · {{ $assignment->schoolClass->name }}</span>
                                    @can('master.manage')
                                        <form method="POST" action="{{ route('master-data.assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus penugasan ini? Riwayat nilai dan absensi tidak ikut dihapus.')">
                                            @csrf @method('DELETE')<button class="text-red-700 underline">Hapus</button>
                                        </form>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @can('master.manage')
                        @if($teacher->user)
                            <details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-indigo-700">Ubah profil dan akun</summary>
                                <form method="POST" action="{{ route('master-data.teachers.update', $teacher) }}" class="mt-2 space-y-2">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $teacher->name }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama guru">
                                    <input name="username" value="{{ $teacher->user->username }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama pengguna">
                                    <input name="email" type="email" value="{{ $teacher->user->email }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="Email">
                                    <div class="grid grid-cols-2 gap-2">
                                        <input name="nip" value="{{ $teacher->nip }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="NIP">
                                        <input name="phone" value="{{ $teacher->phone }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="Telepon">
                                    </div>
                                    <label class="block text-xs text-slate-600">Kata sandi baru (kosongkan jika tidak diubah)</label>
                                    <input name="password" type="password" autocomplete="new-password" minlength="12" class="w-full rounded border px-2 py-1 text-xs" aria-label="Kata sandi baru">
                                    <input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" class="w-full rounded border px-2 py-1 text-xs" aria-label="Ulangi kata sandi baru">
                                    <select name="is_active" class="w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Status akun guru">
                                        <option value="1" @selected($teacher->is_active)>Aktif</option><option value="0" @selected(! $teacher->is_active)>Nonaktif</option>
                                    </select>
                                    <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white">Simpan perubahan</button>
                                </form>
                            </details>
                        @endif
                    @endcan
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">{{ $search !== '' ? 'Tidak ada guru yang cocok dengan pencarian.' : 'Belum ada guru.' }}</p>
            @endforelse
        </div>
    </section>
    @elseif($section === 'penugasan')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
        <div class="border-b px-5 py-4"><h2 class="font-semibold text-slate-900">Penugasan semester aktif</h2><p class="mt-1 text-sm text-slate-500">{{ $semester ? $semester->label() : 'Belum ada semester aktif' }} · {{ $assignments->count() }} penugasan</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="px-5 py-3 font-medium">Guru</th><th class="px-5 py-3 font-medium">Mata pelajaran</th><th class="px-5 py-3 font-medium">Kelas</th>@can('master.manage')<th class="px-5 py-3 font-medium">Aksi</th>@endcan</tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-t"><td class="px-5 py-3 font-medium">{{ $assignment->teacher->name }}</td><td class="px-5 py-3">{{ $assignment->subject->name }}</td><td class="px-5 py-3">{{ $assignment->schoolClass->name }}</td>@can('master.manage')<td class="px-5 py-3"><form method="POST" action="{{ route('master-data.assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus penugasan ini? Nilai dan absensi yang sudah tercatat tetap tersimpan.')">@csrf @method('DELETE')<button class="text-xs font-medium text-red-700 hover:underline">Hapus penugasan</button></form></td>@endcan</tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->can('master.manage') ? 4 : 3 }}" class="px-5 py-10 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada penugasan yang cocok dengan pencarian.' : 'Belum ada penugasan untuk semester aktif.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    @if($section === 'orang-tua')
    <section class="overflow-hidden rounded-xl border bg-white">
        <h2 class="border-b px-5 py-4 font-semibold">Orang tua/wali <span class="text-sm font-normal text-slate-500">({{ $guardians->count() }})</span></h2>
        <div class="divide-y">
            @forelse($guardians as $guardian)
                <div class="px-5 py-3 text-sm">
                    <p class="font-medium">{{ $guardian->name }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $guardian->user?->username ? '@'.$guardian->user->username : 'Belum ada akun masuk' }} · {{ $guardian->user?->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    <p class="mt-1 text-xs text-indigo-700">Anak: {{ $guardian->students->pluck('name')->join(', ') ?: 'Belum terhubung' }}</p>
                    @can('master.manage')
                        @if($guardian->user)
                            <details class="mt-2"><summary class="cursor-pointer text-xs font-medium text-indigo-700">Ubah profil, akun, dan relasi anak</summary>
                                <form method="POST" action="{{ route('master-data.guardians.update', $guardian) }}" class="mt-2 space-y-2">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $guardian->name }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama orang tua/wali">
                                    <input name="username" value="{{ $guardian->user->username }}" required class="w-full rounded border px-2 py-1 text-xs" aria-label="Nama pengguna">
                                    <input name="email" type="email" value="{{ $guardian->user->email }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="Email">
                                    <input name="phone" value="{{ $guardian->phone }}" class="w-full rounded border px-2 py-1 text-xs" aria-label="Nomor telepon">
                                    <label class="block text-xs text-slate-600">Kata sandi baru (opsional)</label>
                                    <input name="password" type="password" autocomplete="new-password" minlength="12" class="w-full rounded border px-2 py-1 text-xs" aria-label="Kata sandi baru">
                                    <input name="password_confirmation" type="password" autocomplete="new-password" minlength="12" class="w-full rounded border px-2 py-1 text-xs" aria-label="Ulangi kata sandi baru">
                                    <select name="is_active" class="w-full rounded border bg-white px-2 py-1 text-xs" aria-label="Status akun">
                                        <option value="1" @selected($guardian->user->is_active)>Aktif</option><option value="0" @selected(! $guardian->user->is_active)>Nonaktif</option>
                                    </select>
                                    <label class="block text-xs font-medium">Hubungan dengan semua anak terpilih</label>
                                    @php($relationship = $guardian->students->first()?->pivot?->relationship ?? 'wali')
                                    <select name="relationship" required class="w-full rounded border bg-white px-2 py-1 text-xs">
                                        @foreach(['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'] as $value => $label)<option value="{{ $value }}" @selected($relationship === $value)>{{ $label }}</option>@endforeach
                                    </select>
                                    <div class="max-h-36 space-y-1 overflow-y-auto rounded border p-2">
                                        @foreach($allStudents as $child)
                                            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="student_ids[]" value="{{ $child->id }}" @checked($guardian->students->contains('id', $child->id))> {{ $child->name }} · NIS {{ $child->nis }}</label>
                                        @endforeach
                                    </div>
                                    @error('student_ids')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                                    @error('password')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                                    <button class="rounded bg-indigo-600 px-3 py-1 text-xs text-white">Simpan perubahan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('master-data.guardians.destroy', $guardian) }}" class="mt-2" onsubmit="return confirm('Arsipkan akun ini? Relasi anak tetap tersimpan.')">
                                @csrf @method('DELETE')<button class="text-xs text-red-700 underline">Arsipkan akun</button>
                            </form>
                        @endif
                    @endcan
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">{{ $search !== '' ? 'Tidak ada orang tua/wali yang cocok dengan pencarian.' : 'Belum ada akun orang tua/wali.' }}</p>
            @endforelse
        </div>
    </section>
    @endif
</div>
@endif
@endsection
