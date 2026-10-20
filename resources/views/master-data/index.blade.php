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
        ['key' => 'kelas', 'title' => 'Kelas', 'description' => 'Tingkat kelas, tahun ajaran, dan wali kelas.'],
        ['key' => 'guru', 'title' => 'Guru', 'description' => 'Profil, akun guru, dan status akun.'],
        ['key' => 'mapel', 'title' => 'Mata Pelajaran', 'description' => 'Kode, nama, dan status mata pelajaran.'],
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
    @if($section)
        <nav aria-label="Submenu data master" class="flex flex-wrap gap-2 rounded-xl border border-slate-200 bg-white p-2">
            <a href="{{ route('master-data.section', ['section' => $section, 'tab' => 'data']) }}" @class(['rounded-lg px-4 py-2 text-sm font-semibold transition', $dataTab === 'data' ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50']) @if($dataTab === 'data') aria-current="page" @endif>Data {{ $sectionLabels[$section] }}</a>
            @can('master.manage')<a href="{{ route('master-data.section', ['section' => $section, 'tab' => 'upload']) }}" @class(['rounded-lg px-4 py-2 text-sm font-semibold transition', $dataTab === 'upload' ? 'bg-indigo-600 text-white' : 'border border-slate-200 text-slate-700 hover:bg-slate-50']) @if($dataTab === 'upload') aria-current="page" @endif>Tambah {{ $sectionLabels[$section] }}</a>@endcan
        </nav>
    @endif
    @if($dataTab !== 'upload')
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
    @endif
@endif

@if($section)
@can('master.manage')
    @if($dataTab === 'upload')
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
                        <label for="grade_level" class="mb-1 block text-sm font-medium">Tingkat Kelas</label>
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
        @if($dataTab === 'upload')
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-semibold">Tambah siswa</h2>
            @if($classes->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Buat kelas terlebih dahulu sebelum mendaftarkan siswa.</p>
            @else
                <form method="POST" action="{{ route('master-data.students.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="student_nis" class="mb-1 block text-sm font-medium">NIS</label>
                        <input id="student_nis" name="nis" value="{{ old('nis') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('nis')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_nisn" class="mb-1 block text-sm font-medium">NISN (10 digit, opsional)</label>
                        <input id="student_nisn" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('nisn')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_name" class="mb-1 block text-sm font-medium">Nama lengkap (sesuai akta kelahiran)</label>
                        <input id="student_name" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="gender" class="mb-1 block text-sm font-medium">Jenis kelamin</label>
                            <select id="gender" name="gender" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                                <option value="">Pilih</option><option value="L" @selected(old('gender') === 'L')>Laki-laki</option><option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                            </select>
                            @error('gender')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="birth_date" class="mb-1 block text-sm font-medium">Tanggal lahir</label>
                            <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('birth_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="birth_place" class="mb-1 block text-sm font-medium">Tempat lahir</label>
                            <input id="birth_place" name="birth_place" value="{{ old('birth_place') }}" maxlength="100" class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('birth_place')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_religion" class="mb-1 block text-sm font-medium">Agama</label>
                            <input id="student_religion" name="religion" value="{{ old('religion') }}" maxlength="50" placeholder="Contoh: Islam" class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('religion')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_nik" class="mb-1 block text-sm font-medium">NIK (opsional)</label>
                            <input id="student_nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" placeholder="16 digit" class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('nik')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_photo" class="mb-1 block text-sm font-medium">Foto siswa (opsional)</label>
                            <input id="student_photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border bg-white px-3 py-2 text-sm">
                            <p class="mt-1 text-xs text-slate-500">JPG, PNG, atau WebP · maksimal 2 MB</p>
                            @error('photo')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="student_registration_date" class="mb-1 block text-sm font-medium">Tanggal terdaftar</label>
                            <input id="student_registration_date" name="registration_date" type="date" max="{{ now()->toDateString() }}" value="{{ old('registration_date', now()->toDateString()) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                            @error('registration_date')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="student_phone" class="mb-1 block text-sm font-medium">Nomor HP siswa (opsional)</label>
                        <input id="student_phone" name="phone" value="{{ old('phone') }}" maxlength="20" class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <details class="rounded-lg border p-3" open>
                        <summary class="cursor-pointer text-sm font-semibold">Data orang tua/wali</summary>
                        <p class="mt-1 text-xs text-slate-500">Nama Ayah atau Ibu wajib diisi minimal salah satu. Wali boleh ditambahkan jika berbeda.</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach(['father' => 'Ayah', 'mother' => 'Ibu', 'guardian' => 'Wali (opsional)'] as $prefix => $label)
                                <fieldset class="space-y-2 rounded-lg border p-3">
                                    <legend class="px-1 text-xs font-semibold">{{ $label }}</legend>
                                    <input name="{{ $prefix }}_name" value="{{ old($prefix.'_name') }}" placeholder="Nama {{ strtolower($label) }}" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Nama {{ $label }}">
                                    <input name="{{ $prefix }}_phone" value="{{ old($prefix.'_phone') }}" placeholder="Nomor HP" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Nomor HP {{ $label }}">
                                    <input name="{{ $prefix }}_occupation" value="{{ old($prefix.'_occupation') }}" maxlength="100" placeholder="Pekerjaan" class="w-full rounded border px-2 py-1.5 text-sm" aria-label="Pekerjaan {{ $label }}">
                                    @error($prefix.'_name')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                                    @error($prefix.'_phone')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                                    @error($prefix.'_occupation')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                                </fieldset>
                            @endforeach
                        </div>
                    </details>
                    <div class="rounded-lg bg-indigo-50 p-3 text-xs text-indigo-900">
                        Email siswa dibuat otomatis setelah disimpan. Kata sandi awal adalah tanggal lahir dengan format tahun-bulan-tanggal (YYYY-MM-DD).
                    </div>
                    <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan siswa</button>
                </form>
            @endif
        </section>
        @endif
        @if($dataTab === 'upload')
        <section class="rounded-xl border border-indigo-100 bg-white p-5">
            <h2 class="font-semibold">Impor siswa dari Excel/CSV</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Isi template di Excel lalu simpan sebagai CSV UTF-8. Ayah atau Ibu harus terisi minimal salah satu. Email dan akun dibuat otomatis; maksimal 2.000 baris per unggahan.</p>
            <a href="{{ route('master-data.students.template') }}" class="mt-3 inline-flex rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Unduh template CSV</a>
            <form method="POST" action="{{ route('master-data.students.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm" aria-label="File CSV data siswa">
                @error('file')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Unggah siswa</button>
            </form>
        </section>
        @endif
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
        @if(in_array($section, ['kelas', 'guru', 'mapel', 'penugasan'], true))
        <section class="rounded-xl border border-indigo-100 bg-white p-5">
            <h2 class="font-semibold">Upload {{ strtolower($sectionLabels[$section]) }} dari CSV</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Unduh template, isi di Excel, lalu simpan sebagai CSV UTF-8. @if($section === 'guru') Kata sandi awal wajib minimal 12 karakter. @elseif($section === 'penugasan') Guru, kelas, dan kode mapel harus sudah tersedia pada semester aktif. @endif</p>
            <a href="{{ route('master-data.import-template', $section) }}" class="mt-3 inline-flex rounded-lg border border-indigo-200 px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Unduh template CSV</a>
            <form method="POST" action="{{ route('master-data.import', $section) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                @csrf
                <input type="file" name="file" accept=".csv,text/csv" required class="w-full rounded-lg border bg-white px-3 py-2 text-sm" aria-label="File CSV {{ strtolower($sectionLabels[$section]) }}">
                @error('file')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
                <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Unggah {{ strtolower($sectionLabels[$section]) }}</button>
            </form>
        </section>
        @endif
    </div>
    @endif
@endcan

<div class="space-y-5">
    @if($section === 'kelas' && $dataTab === 'data')
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
            <h2 class="font-semibold">Kelas <span class="text-sm font-normal text-slate-500">({{ $classes->count() }})</span></h2>
            @can('master.manage')<a href="{{ route('master-data.classes.export', request()->only(['q'])) }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100"><span aria-hidden="true">↓</span> Ekspor CSV</a>@endcan
        </div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Nama kelas</th><th class="px-4 py-3">Tingkat</th><th class="px-4 py-3">Tahun ajaran</th><th class="px-4 py-3">Wali kelas</th><th class="px-4 py-3">Siswa</th>@can('master.manage')<th class="px-4 py-3">Kelola</th>@endcan</tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($classes as $class)
                <tr><td class="whitespace-nowrap px-4 py-3 font-medium">{{ $class->name }}</td><td class="px-4 py-3">{{ $class->grade_level }}</td><td class="whitespace-nowrap px-4 py-3">{{ $class->academic_year }}</td><td class="whitespace-nowrap px-4 py-3">{{ $class->homeroomTeacher?->name ?? '—' }}</td><td class="px-4 py-3">{{ $class->enrollments_count }}</td>@can('master.manage')<td class="px-4 py-3"><a href="{{ route('master-data.classes.edit', $class) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Kelola</a></td>@endcan</tr>
            @empty<tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada kelas yang cocok dengan pencarian.' : 'Belum ada kelas.' }}</td></tr>@endforelse
        </tbody></table></div>
    </section>
    @endif

    @if($section === 'siswa' && $dataTab === 'data')
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
            <div><h2 class="font-semibold">Siswa <span class="text-sm font-normal text-slate-500">({{ $students->total() }})</span></h2><p class="mt-1 text-xs text-slate-500">Urut dari tahun dan tanggal terdaftar terbaru.</p></div>
            @can('master.manage')<a href="{{ route('master-data.students.export', request()->only(['q', 'entry_year'])) }}" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100"><span aria-hidden="true">↓</span> Ekspor CSV</a>@endcan
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">NIS / NISN</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Tanggal daftar</th><th class="px-4 py-3">Email login</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Kelola</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php($tableEnrollment = $student->enrollments->sortByDesc('academic_year')->first())
                        <tr class="align-middle">
                            <td class="whitespace-nowrap px-4 py-3"><div class="flex items-center gap-2">@if($student->photo_path)<img src="{{ asset('storage/'.$student->photo_path) }}" alt="Foto {{ $student->name }}" class="size-9 rounded-full object-cover">@else<span class="grid size-9 place-items-center rounded-full bg-indigo-50 font-semibold text-indigo-700">{{ mb_substr($student->name, 0, 1) }}</span>@endif<span class="font-medium">{{ $student->name }}</span></div></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">{{ $student->nis }}<br>{{ $student->nisn ?: '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $tableEnrollment?->schoolClass?->name ?? 'Belum ada kelas' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $student->registration_date?->format('d/m/Y') ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3 text-xs text-indigo-700">{{ $student->user?->email ?? 'Belum dibuat' }}</td><td class="whitespace-nowrap px-4 py-3">{{ ucfirst($student->status) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">@can('master.manage')<a href="{{ route('master-data.students.edit', $student) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Ubah / CRUD</a>@else<span class="text-xs text-slate-400">Lihat</span>@endcan</td>
                        </tr>
                    @empty<tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada siswa yang cocok dengan pencarian.' : 'Belum ada siswa.' }}</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())<div class="border-t px-5 py-3">{{ $students->links() }}</div>@endif
    </section>
    @endif

    @if($section === 'mapel' && $dataTab === 'data')
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4"><h2 class="font-semibold">Mata pelajaran <span class="text-sm font-normal text-slate-500">({{ $subjects->count() }})</span></h2>@can('master.manage')<a href="{{ route('master-data.subjects.export', request()->only(['q'])) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">↓ Ekspor CSV</a>@endcan</div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Mata pelajaran</th><th class="px-4 py-3">Status</th>@can('master.manage')<th class="px-4 py-3">Kelola</th>@endcan</tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($subjects as $subject)
                <tr><td class="px-4 py-3">{{ $subject->code ?? '—' }}</td><td class="px-4 py-3 font-medium">{{ $subject->name }}</td><td class="px-4 py-3">{{ $subject->is_active ? 'Aktif' : 'Nonaktif' }}</td>@can('master.manage')<td class="px-4 py-3"><a href="{{ route('master-data.subjects.edit', $subject) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Kelola</a></td>@endcan</tr>
            @empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada mata pelajaran yang cocok dengan pencarian.' : 'Belum ada mata pelajaran.' }}</td></tr>@endforelse
        </tbody></table></div>
    </section>
    @endif

    @if($section === 'guru' && $dataTab === 'data')
    <section class="overflow-hidden rounded-xl border bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4"><h2 class="font-semibold">Guru <span class="text-sm font-normal text-slate-500">({{ $teacherProfiles->count() }})</span></h2>@can('master.manage')<a href="{{ route('master-data.teachers.export', request()->only(['q'])) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">↓ Ekspor CSV</a>@endcan</div>
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Nama guru</th><th class="px-4 py-3">NIP</th><th class="px-4 py-3">Username</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">HP</th><th class="px-4 py-3">Status</th>@can('master.manage')<th class="px-4 py-3">Kelola</th>@endcan</tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($teacherProfiles as $teacher)
                <tr><td class="whitespace-nowrap px-4 py-3 font-medium">{{ $teacher->name }}</td><td class="whitespace-nowrap px-4 py-3">{{ $teacher->nip ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $teacher->user?->username ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $teacher->user?->email ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $teacher->phone ?? '—' }}</td><td class="whitespace-nowrap px-4 py-3">{{ $teacher->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    @can('master.manage')<td class="px-4 py-3">@if($teacher->user)<a href="{{ route('master-data.teachers.edit', $teacher) }}" class="rounded-lg border border-indigo-200 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">Kelola</a>@endif</td>@endcan
                </tr>
            @empty<tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada guru yang cocok dengan pencarian.' : 'Belum ada guru.' }}</td></tr>@endforelse
        </tbody></table></div>
    </section>
    @elseif($section === 'penugasan' && $dataTab === 'data')
    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4"><div><h2 class="font-semibold text-slate-900">Penugasan semester aktif</h2><p class="mt-1 text-sm text-slate-500">{{ $semester ? $semester->label() : 'Belum ada semester aktif' }} · {{ $assignments->count() }} penugasan</p></div>@can('master.manage')<a href="{{ route('master-data.assignments.export', request()->only(['q'])) }}" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">↓ Ekspor CSV</a>@endcan</div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="px-5 py-3 font-medium">Guru</th><th class="px-5 py-3 font-medium">Mata pelajaran</th><th class="px-5 py-3 font-medium">Kelas</th>@can('master.manage')<th class="px-5 py-3 font-medium">Aksi</th>@endcan</tr></thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        <tr class="border-t"><td class="px-5 py-3 font-medium">{{ $assignment->teacher->name }}</td><td class="px-5 py-3">{{ $assignment->subject->name }}</td><td class="px-5 py-3">{{ $assignment->schoolClass->name }}</td>@can('master.manage')<td class="px-5 py-3"><a href="{{ route('master-data.assignments.edit', $assignment) }}" class="text-xs font-semibold text-indigo-700 hover:underline">Kelola</a></td>@endcan</tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->can('master.manage') ? 4 : 3 }}" class="px-5 py-10 text-center text-slate-500">{{ $search !== '' ? 'Tidak ada penugasan yang cocok dengan pencarian.' : 'Belum ada penugasan untuk semester aktif.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

</div>
@endif
@endsection
