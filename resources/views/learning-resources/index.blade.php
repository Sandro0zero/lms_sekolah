@extends('layouts.app')

@section('title', ($pageKind === 'material' ? 'Materi Pelajaran' : 'Library') . ' | LMS Sekolah')

@section('content')
<section class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
    <div>
        <p class="text-sm font-semibold text-indigo-600">Pusat belajar</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ $pageKind === 'material' ? 'Materi Pelajaran' : 'Library' }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $pageKind === 'material' ? 'Materi yang diunggah guru untuk kelas dan mata pelajaran.' : 'Koleksi ebook dan artikel PDF yang tersedia untuk warga sekolah.' }}</p>
    </div>
    <div class="text-sm text-slate-500">{{ $resources->total() }} dokumen</div>
</section>

@if($pageKind === 'material')
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <form method="GET" action="{{ route('learning-resources.materials') }}" class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
        <label class="text-sm font-semibold text-slate-700">Pilih kelas
            <select name="class_id" onchange="this.form.submit()" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                <option value="">Semua kelas yang dapat diakses</option>
                @foreach($classes as $class)<option value="{{ $class->id }}" @selected($selectedClassId === $class->id)>Kelas {{ $class->name }} · {{ $class->academic_year }}</option>@endforeach
            </select>
        </label>
        <label class="text-sm font-semibold text-slate-700">Pilih mata pelajaran
            <select name="subject_id" onchange="this.form.submit()" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
                <option value="">Semua mata pelajaran</option>
                @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subject->name }}</option>@endforeach
            </select>
        </label>
        <label class="self-end text-sm font-semibold text-slate-700">Cari materi
            <input name="q" value="{{ request('q') }}" placeholder="Judul atau kategori" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm">
        </label>
    </form>
</section>
@else
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <form method="GET" action="{{ route('learning-resources.library') }}" class="grid gap-3 md:grid-cols-[1fr_190px_auto]">
        <label class="sr-only" for="resource-search">Cari dokumen</label><input id="resource-search" name="q" value="{{ request('q') }}" placeholder="Cari judul, kategori, atau deskripsi..." class="rounded-xl border-slate-300 text-sm">
        <select name="type" class="rounded-xl border-slate-300 text-sm"><option value="">Semua koleksi</option><option value="ebook" @selected(request('type') === 'ebook')>Ebook</option><option value="article" @selected(request('type') === 'article')>Artikel PDF</option></select>
        <button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cari</button>
    </form>
</section>
@endif

@if($canUpload)
<section class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-5"><h2 class="text-lg font-bold text-slate-900">{{ $pageKind === 'material' ? 'Unggah materi' : 'Tambah koleksi library' }}</h2><p class="mt-1 text-sm text-slate-500">{{ $pageKind === 'material' ? 'File PDF, Word, atau PowerPoint. Maksimal 20 MB.' : 'Library menerima PDF saja. Maksimal 20 MB.' }}</p></div>
    <form method="POST" action="{{ route($pageKind === 'material' ? 'learning-resources.materials.store' : 'learning-resources.library.store') }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
        @csrf
        <label class="text-sm font-semibold text-slate-700">Judul<input name="title" required maxlength="180" value="{{ old('title') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm" placeholder="Judul dokumen"></label>
        @if($pageKind === 'library')
            <label class="text-sm font-semibold text-slate-700">Jenis koleksi<select name="library_type" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"><option value="ebook">Ebook</option><option value="article">Artikel PDF</option></select></label>
        @endif
        @if($pageKind === 'material')
            @if($selectedClassId)<input type="hidden" name="class_id" value="{{ $selectedClassId }}">@endif
            <label class="text-sm font-semibold text-slate-700">Mata pelajaran<select name="subject_id" @required(auth()->user()->hasRole('guru')) class="mt-1.5 w-full rounded-xl border-slate-300 text-sm"><option value="">{{ auth()->user()->hasRole('guru') ? 'Pilih mata pelajaran' : 'Semua mata pelajaran' }}</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subject->name }}</option>@endforeach</select></label>
        @endif
        <label class="text-sm font-semibold text-slate-700">Kategori <span class="font-normal text-slate-400">(opsional)</span><input name="category" maxlength="100" value="{{ old('category') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm" placeholder="Contoh: IPA, Referensi, Sejarah"></label>
        <label class="text-sm font-semibold text-slate-700 md:col-span-2">Deskripsi <span class="font-normal text-slate-400">(opsional)</span><textarea name="description" rows="2" maxlength="2000" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm" placeholder="Ringkasan isi dokumen">{{ old('description') }}</textarea></label>
        <label class="text-sm font-semibold text-slate-700 md:col-span-2">Pilih file<input type="file" name="file" required accept="{{ $pageKind === 'library' ? '.pdf' : '.pdf,.doc,.docx,.ppt,.pptx' }}" class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white p-2 text-sm"></label>
        <div class="md:col-span-2"><button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Simpan {{ $pageKind === 'material' ? 'materi' : 'koleksi' }}</button></div>
    </form>
</section>
@elseif($pageKind === 'material' && auth()->user()->hasRole('guru'))
    <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">@if(!$activeSemester)Aktifkan semester terlebih dahulu agar guru dapat mengunggah materi.@elseif(!$selectedClassId)Pilih kelas yang Anda ajar terlebih dahulu untuk mengunggah materi.@elseTidak ada penugasan mengajar aktif untuk kelas tersebut.@endif</p>
@endif

@if($resources->isEmpty())
    <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><div class="mx-auto grid size-12 place-items-center rounded-2xl bg-indigo-50 text-xl text-indigo-600">▤</div><h2 class="mt-4 font-bold text-slate-900">Belum ada {{ $pageKind === 'material' ? 'materi' : 'koleksi library' }}</h2><p class="mt-1 text-sm text-slate-500">Dokumen yang sesuai dengan pencarian akan tampil di sini.</p></section>
@else
<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @foreach($resources as $resource)
    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-violet-50 text-violet-700' => $pageKind === 'library', 'bg-sky-50 text-sky-700' => $pageKind === 'material'])>{{ $pageKind === 'library' ? ($resource->library_type === 'ebook' ? 'Ebook' : 'Artikel PDF') : 'Materi Pelajaran' }}</span><span class="text-xs text-slate-400">{{ number_format($resource->file_size / 1048576, 1) }} MB</span></div>
        <h2 class="mt-4 line-clamp-2 text-lg font-bold text-slate-900">{{ $resource->title }}</h2>
        <p class="mt-2 line-clamp-3 min-h-14 text-sm leading-6 text-slate-600">{{ $resource->description ?: 'Tidak ada deskripsi.' }}</p>
        <div class="mt-4 flex flex-wrap gap-1.5 text-xs">@if($resource->category)<span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">{{ $resource->category }}</span>@endif @if($resource->subject)<span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">{{ $resource->subject->name }}</span>@endif @if($resource->schoolClass)<span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-600">Kelas {{ $resource->schoolClass->name }}</span>@else<span class="rounded-lg bg-emerald-50 px-2 py-1 text-emerald-700">Umum</span>@endif</div>
        <p class="mt-3 truncate text-xs text-slate-400" title="{{ $resource->original_filename }}">{{ $resource->original_filename }}</p>
        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4"><span class="text-xs text-slate-400">{{ $resource->uploader?->name ? 'Diunggah '.$resource->uploader->name : 'Diunggah' }} · {{ $resource->created_at->format('d M Y') }}</span><a href="{{ route($pageKind === 'material' ? 'learning-resources.materials.open' : 'learning-resources.library.open', $resource) }}" target="_blank" rel="noopener" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-bold text-white hover:bg-indigo-700">{{ $resource->mime_type === 'application/pdf' ? 'Buka PDF' : 'Unduh materi' }}</a></div>
        @if($canManage)
        <details class="mt-4 border-t border-slate-100 pt-3"><summary class="cursor-pointer text-xs font-semibold text-slate-500 hover:text-indigo-700">Ubah data / hapus</summary>
            <form method="POST" action="{{ route($pageKind === 'material' ? 'learning-resources.materials.update' : 'learning-resources.library.update', $resource) }}" enctype="multipart/form-data" class="mt-3 space-y-3">@csrf @method('PUT')
                @if($pageKind === 'library')<select name="library_type" required class="w-full rounded-lg border-slate-300 text-xs"><option value="ebook" @selected($resource->library_type === 'ebook')>Ebook</option><option value="article" @selected($resource->library_type === 'article')>Artikel PDF</option></select>@endif
                <input name="title" required maxlength="180" value="{{ $resource->title }}" class="w-full rounded-lg border-slate-300 text-xs"><input name="category" maxlength="100" value="{{ $resource->category }}" placeholder="Kategori" class="w-full rounded-lg border-slate-300 text-xs"><textarea name="description" rows="2" maxlength="2000" placeholder="Deskripsi" class="w-full rounded-lg border-slate-300 text-xs">{{ $resource->description }}</textarea>
                @if($pageKind === 'material')<select name="subject_id" class="w-full rounded-lg border-slate-300 text-xs"><option value="">Semua mapel</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($resource->subject_id === $subject->id)>{{ $subject->name }}</option>@endforeach</select>@endif
                <label class="block text-xs text-slate-500">Ganti file (opsional)<input type="file" name="file" accept="{{ $pageKind === 'library' ? '.pdf' : '.pdf,.doc,.docx,.ppt,.pptx' }}" class="mt-1 block w-full text-xs"></label><button class="w-full rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white">Simpan perubahan</button>
            </form>
            <form method="POST" action="{{ route($pageKind === 'material' ? 'learning-resources.materials.destroy' : 'learning-resources.library.destroy', $resource) }}" class="mt-2" onsubmit="return confirm('Hapus dokumen ini secara permanen?')">@csrf @method('DELETE')<button class="w-full rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Hapus dokumen</button></form>
        </details>
        @endif
    </article>
    @endforeach
</section>
<div>{{ $resources->links() }}</div>
@endif
@endsection
