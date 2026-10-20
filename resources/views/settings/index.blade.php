@extends('layouts.app')

@section('title', 'Pengaturan · LMS Sekolah')

@section('content')
<div>
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengaturan Akademik dan KPI</h1>
    <p class="mt-1 text-sm text-slate-600">Perubahan pengaturan berlaku pada perhitungan berikutnya. KPI yang sudah dipublikasikan menyimpan bobotnya sendiri.</p>
</div>

<section class="rounded-xl border bg-white p-5">
    <div>
        <h2 class="font-semibold">Bobot nilai standar sekolah</h2>
        <p class="mt-1 text-xs text-slate-500">Dipakai jika bobot khusus mata pelajaran belum diatur. Total harus 100%.</p>
    </div>
    <form method="POST" action="{{ route('settings.academic.update') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @csrf
        @foreach(['uh' => 'Ulangan harian', 'uts' => 'UTS', 'uas' => 'UAS'] as $key => $label)
            <div>
                <label for="grade_{{ $key }}" class="mb-1 block text-sm font-medium">{{ $label }} (%)</label>
                <input id="grade_{{ $key }}" name="grade_{{ $key }}" type="number" min="0" max="100" value="{{ old('grade_'.$key, $gradeWeights[$key]) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
            </div>
        @endforeach
        <div>
            <label for="default_kkm" class="mb-1 block text-sm font-medium">KKM standar</label>
            <input id="default_kkm" name="default_kkm" type="number" min="0" max="100" step="0.01" value="{{ old('default_kkm', $defaultKkm) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
        </div>
        <div class="flex items-end"><button class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan standar nilai</button></div>
    </form>
    @error('grade_uh')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('grade_uts')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('grade_uas')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('default_kkm')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('grade_total')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
</section>

<section class="rounded-xl border bg-white p-5">
    <div>
        <h2 class="font-semibold">Bobot KPI dan perilaku</h2>
        <p class="mt-1 text-xs text-slate-500">Bobot KPI harus berjumlah 100%. Kontribusi absensi diisi antara 0 dan 1.</p>
    </div>
    <form method="POST" action="{{ route('settings.kpi.update') }}" class="mt-4 space-y-5">
        @csrf
        <div class="grid gap-3 sm:grid-cols-3">
            @foreach(['grade' => 'Nilai', 'attendance' => 'Kehadiran', 'behavior' => 'Perilaku'] as $key => $label)
                <div>
                    <label for="kpi_{{ $key }}" class="mb-1 block text-sm font-medium">{{ $label }} (%)</label>
                    <input id="kpi_{{ $key }}" name="kpi_{{ $key }}" type="number" min="0" max="100" value="{{ old('kpi_'.$key, $kpiWeights[$key]) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                </div>
            @endforeach
        </div>
        @error('kpi_grade')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        @error('kpi_attendance')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        @error('kpi_behavior')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
        @error('kpi_total')<p class="text-sm text-red-700">{{ $message }}</p>@enderror

        <div class="border-t pt-4">
            <h3 class="text-sm font-semibold">Dampak status absensi pada skor kehadiran</h3>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                @foreach(['hadir' => 'Hadir', 'sakit' => 'Sakit', 'izin' => 'Izin', 'alfa' => 'Alfa', 'bolos' => 'Bolos'] as $key => $label)
                    <div>
                        <label for="impact_{{ $key }}" class="mb-1 block text-sm font-medium">{{ $label }}</label>
                        <input id="impact_{{ $key }}" name="impact_{{ $key }}" type="number" min="0" max="1" step="0.01" value="{{ old('impact_'.$key, $attendanceImpact[$key]) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                        @error('impact_'.$key)<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </div>
        <div class="grid gap-3 border-t pt-4 sm:grid-cols-2">
            <div>
                <label for="behavior_scale" class="mb-1 block text-sm font-medium">Skala maksimal perilaku</label>
                <input id="behavior_scale" name="behavior_scale" type="number" min="2" max="10" value="{{ old('behavior_scale', $behaviorScale) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                @error('behavior_scale')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="homeroom_weight" class="mb-1 block text-sm font-medium">Pengali bobot wali kelas</label>
                <input id="homeroom_weight" name="homeroom_weight" type="number" min="0" max="5" step="0.1" value="{{ old('homeroom_weight', $homeroomWeight) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
                @error('homeroom_weight')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan pengaturan KPI</button>
    </form>
</section>

<section class="rounded-xl border bg-white p-5">
    <div>
        <h2 class="font-semibold">Batas perubahan data</h2>
        <p class="mt-1 text-xs text-slate-500">Dihitung dari waktu input pertama, sehingga penyuntingan tidak memperpanjang batas.</p>
    </div>
    <form method="POST" action="{{ route('settings.edit-windows.update') }}" class="mt-4 grid gap-3 sm:grid-cols-3">
        @csrf
        <div>
            <label for="grade_edit_days" class="mb-1 block text-sm font-medium">Ubah nilai (hari)</label>
            <input id="grade_edit_days" name="grade_edit_days" type="number" min="0" max="90" value="{{ old('grade_edit_days', $gradeEditDays) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
            @error('grade_edit_days')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="attendance_edit_days" class="mb-1 block text-sm font-medium">Ubah absensi (hari)</label>
            <input id="attendance_edit_days" name="attendance_edit_days" type="number" min="0" max="90" value="{{ old('attendance_edit_days', $attendanceEditDays) }}" required class="w-full rounded-lg border px-3 py-2 text-sm">
            @error('attendance_edit_days')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-end"><button class="w-full rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-slate-50">Simpan batas edit</button></div>
    </form>
</section>

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="border-b px-5 py-4">
        <h2 class="font-semibold">Kategori hasil KPI</h2>
        <p class="mt-1 text-sm text-slate-500">Rentang harus menutup skor 0–100 tanpa celah. Nama kategori yang sudah tersimpan pada KPI publik tetap menjadi snapshot.</p>
    </div>
    <form method="POST" action="{{ route('settings.kpi-categories.update') }}" onsubmit="return confirm('Simpan perubahan kategori KPI? Rentang harus tetap mencakup seluruh skor 0–100.')">
        @csrf @method('PUT')
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">Nama kategori</th><th class="px-4 py-3">Skor minimum</th><th class="px-4 py-3">Skor maksimum</th><th class="px-4 py-3">Urutan</th><th class="px-4 py-3">Hapus</th></tr></thead>
                <tbody>
                    @foreach($kpiCategories as $category)
                        <tr class="border-t">
                            <td class="px-4 py-3"><input type="hidden" name="categories[{{ $category->id }}][id]" value="{{ $category->id }}"><input name="categories[{{ $category->id }}][name]" value="{{ old('categories.'.$category->id.'.name', $category->name) }}" required maxlength="50" class="w-full min-w-40 rounded border px-2 py-1.5"></td>
                            <td class="px-4 py-3"><input name="categories[{{ $category->id }}][min_score]" type="number" min="0" max="100" step="0.01" value="{{ old('categories.'.$category->id.'.min_score', number_format($category->min_score, 2, '.', '')) }}" required class="w-28 rounded border px-2 py-1.5"></td>
                            <td class="px-4 py-3"><input name="categories[{{ $category->id }}][max_score]" type="number" min="0" max="100" step="0.01" value="{{ old('categories.'.$category->id.'.max_score', number_format($category->max_score, 2, '.', '')) }}" required class="w-28 rounded border px-2 py-1.5"></td>
                            <td class="px-4 py-3"><input name="categories[{{ $category->id }}][sort_order]" type="number" min="0" max="255" value="{{ old('categories.'.$category->id.'.sort_order', $category->sort_order) }}" required class="w-20 rounded border px-2 py-1.5"></td>
                            <td class="px-4 py-3"><label class="flex items-center gap-2 text-xs"><input type="checkbox" name="deleted_ids[]" value="{{ $category->id }}"> Hapus</label></td>
                        </tr>
                    @endforeach
                    <tr class="border-t bg-slate-50/70">
                        <td class="px-4 py-3"><input name="new_name" value="{{ old('new_name') }}" placeholder="Kategori baru (opsional)" maxlength="50" class="w-full min-w-40 rounded border px-2 py-1.5"></td>
                        <td class="px-4 py-3"><input name="new_min_score" type="number" min="0" max="100" step="0.01" value="{{ old('new_min_score') }}" placeholder="Min." class="w-28 rounded border px-2 py-1.5"></td>
                        <td class="px-4 py-3"><input name="new_max_score" type="number" min="0" max="100" step="0.01" value="{{ old('new_max_score') }}" placeholder="Maks." class="w-28 rounded border px-2 py-1.5"></td>
                        <td class="px-4 py-3"><input name="new_sort_order" type="number" min="0" max="255" value="{{ old('new_sort_order', $kpiCategories->count() + 1) }}" class="w-20 rounded border px-2 py-1.5"></td>
                        <td class="px-4 py-3 text-xs text-slate-400">Tambah</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @error('kpi_categories')<p class="border-t px-4 py-3 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('new_name')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('new_min_score')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('new_max_score')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
        <div class="border-t px-5 py-4"><button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan kategori KPI</button></div>
    </form>
</section>

<section class="overflow-hidden rounded-xl border bg-white">
    <div class="border-b px-5 py-4">
        <h2 class="font-semibold">Bobot per mata pelajaran</h2>
        @if($semester)<p class="mt-1 text-sm text-slate-500">Semester {{ $semester->label() }}. Kosongkan dengan pilihan “Ikuti standar” untuk memakai bobot umum.</p>@else<p class="mt-1 text-sm text-amber-800">Aktifkan semester sebelum mengatur bobot per mapel.</p>@endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">Mata pelajaran</th><th class="px-4 py-3">UH %</th><th class="px-4 py-3">UTS %</th><th class="px-4 py-3">UAS %</th><th class="px-4 py-3">KKM</th><th class="px-4 py-3">Pengaturan</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @forelse($subjects as $subject)
                    @php($weight = $subjectWeights->get($subject->id))
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $subject->name }}</td>
                        <td colspan="6" class="px-4 py-3">
                            <form method="POST" action="{{ route('settings.subject-weight.update') }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                <input aria-label="Bobot UH {{ $subject->name }}" name="weight_uh" type="number" min="0" max="100" value="{{ old('weight_uh', $weight?->weight_uh ?? $gradeWeights['uh']) }}" class="w-20 rounded border px-2 py-1.5" @disabled(! $semester)>
                                <input aria-label="Bobot UTS {{ $subject->name }}" name="weight_uts" type="number" min="0" max="100" value="{{ old('weight_uts', $weight?->weight_uts ?? $gradeWeights['uts']) }}" class="w-20 rounded border px-2 py-1.5" @disabled(! $semester)>
                                <input aria-label="Bobot UAS {{ $subject->name }}" name="weight_uas" type="number" min="0" max="100" value="{{ old('weight_uas', $weight?->weight_uas ?? $gradeWeights['uas']) }}" class="w-20 rounded border px-2 py-1.5" @disabled(! $semester)>
                                <input aria-label="KKM {{ $subject->name }}" name="kkm" type="number" min="0" max="100" step="0.01" value="{{ old('kkm', $weight?->kkm ?? $defaultKkm) }}" class="w-20 rounded border px-2 py-1.5" @disabled(! $semester)>
                                <label class="flex items-center gap-1.5 whitespace-nowrap text-xs text-slate-600">
                                    <input type="checkbox" name="use_default" value="1" @checked(! $weight) @disabled(! $semester)>
                                    Ikuti standar
                                </label>
                                <button @disabled(! $semester) class="rounded-lg border px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50 disabled:opacity-50">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada mata pelajaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @error('weight_uh')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('weight_uts')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('weight_uas')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('kkm')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
    @error('weight_total')<p class="border-t px-4 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
</section>
@endsection
