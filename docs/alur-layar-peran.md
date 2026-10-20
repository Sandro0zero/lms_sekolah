# Alur layar LMS per peran

Dokumen ini adalah rancangan navigasi untuk pengembangan tahap berikutnya. Layar berlabel **Sudah ada** mengikuti route dan view yang saat ini tersedia; label **Direncanakan** berarti perlu dibuat. Semua halaman data tetap membatasi akses di server berdasarkan peran dan relasi pengguna.

## Peta masuk aplikasi

```text
/masuk (sementara)
  └─ /demo → pilih akun demo → dashboard sesuai peran
       ├─ Admin/Kepala sekolah → KPI Siswa
       ├─ Guru → Beranda Guru
       ├─ Orang tua → pilih anak → Rapor anak
       └─ Siswa → Rapor Saya
```

Login saat ini masih berupa pemilih akun demo lokal. Setelah autentikasi sungguhan dipasang, alur dashboard per peran tetap sama.

## Admin

**Menu utama:** Ringkasan, Data Master, Pengaturan, KPI, Laporan, Audit.

1. **Ringkasan (Direncanakan):** jumlah siswa/guru/kelas, semester aktif, kelengkapan nilai dan absensi, KPI yang belum dihitung/dipublikasikan.
2. **Data Master (Direncanakan / Filament):** semester → kelas → siswa/riwayat kelas → guru → mapel → penugasan mengajar → wali dan relasi anak.
3. **Pengaturan (Direncanakan / Filament):** batas edit, bobot nilai/KKM, bobot komponen KPI, dampak absensi, skala perilaku, kategori KPI, periode perilaku.
4. **KPI Siswa (Sudah ada: `kpi.index`):** pilih kelas → tinjau kelengkapan skor → hitung ulang → periksa KPI per siswa → publikasi KPI yang lengkap.
5. **Detail laporan siswa (Sudah ada: `report.show`):** buka dari daftar KPI untuk memeriksa nilai, absensi dan perilaku.
6. **Audit (Direncanakan):** cari menurut pengguna, tanggal, data dan alasan; hanya admin yang boleh melihat rincian perubahan.

Admin boleh mengelola seluruh data, tetapi penghapusan/perubahan penting tetap dicatat. Publikasi KPI harus meminta konfirmasi dan menyimpan pelaku serta waktu.

## Kepala sekolah

**Menu utama:** Ringkasan, KPI, Laporan, Kehadiran Guru (Direncanakan).

1. **Ringkasan (Direncanakan):** status semester, kehadiran guru, progres pengisian nilai, dan KPI seluruh kelas.
2. **KPI Siswa (Sudah ada: `kpi.index`):** filter kelas → tinjau status draft/lengkap → buka laporan siswa → publikasikan hasil yang lengkap.
3. **Laporan sekolah (Direncanakan):** rekap nilai, kehadiran, perilaku, dan hasil KPI per kelas/semester; ekspor PDF/Excel.
4. **Kehadiran Guru (Direncanakan):** rekap check-in/check-out dan tinjau permohonan izin.

Kepala sekolah dapat melihat seluruh kelas dan menjalankan publikasi KPI; pengelolaan akun serta pengaturan global tetap menjadi tugas admin.

## Guru mata pelajaran

**Menu utama:** Beranda, Kelas & Mapel, Absensi Siswa, Nilai, Perilaku (jika ditugaskan), Kehadiran Saya.

1. **Beranda Guru (Sudah ada: `guru.home`):** semester aktif dan daftar kelas/mapel yang ditugaskan.
2. **Pilih kelas dan mapel (Direncanakan):** hanya penugasan guru untuk semester aktif yang ditampilkan.
3. **Absensi (Direncanakan):** pilih tanggal dan pertemuan → daftar siswa di kelas → isi hadir/sakit/izin/alfa/bolos dan keterangan → simpan → konfirmasi. Duplikasi ditolak.
4. **Nilai (Direncanakan):** pilih jenis UH/UTS/UAS dan urutan → isi nilai mentah → validasi 0–100 → simpan → koreksi dalam 7 hari dari input pertama.
5. **Edit lewat batas waktu (Direncanakan):** tampilkan formulir alasan dan minta persetujuan admin; simpan perubahan dan audit lama/baru.
6. **Perilaku (Direncanakan):** hanya pada periode terbuka dan siswa yang dapat dinilai guru; isi setiap aspek dan kirim penilaian.
7. **Kehadiran Saya (Direncanakan):** check-in/out harian dan kirim permohonan izin.
8. **Wali kelas:** dari daftar siswa (Sudah ada tautan laporan) menuju laporan kelas; direncanakan tambah catatan wali dan usulan/status KPI, tanpa mengubah KPI setelah publikasi.

Guru hanya dapat melihat siswa melalui kelas/mapel dalam `teaching_assignments`; menu tidak boleh menjadi satu-satunya lapisan otorisasi.

## Orang tua

**Menu utama:** Anak Saya, Rapor, Pengumuman (Direncanakan).

1. **Pilih anak (Sudah ada: `guardian.index`):** tampil jika wali memiliki lebih dari satu anak; kartu menunjukkan nama, NIS, kelas dan semester. Jika hanya satu anak, langsung ke rapor.
2. **Rapor anak (route ada, tampilan belum berfungsi):** alurnya menuju `report.show`; controller memanggil view yang belum tersedia (`report.show`), sedangkan yang ada `report/index.blade.php`. Isi target: ringkasan nilai per mapel, absensi, perilaku yang boleh dibagikan, KPI dan catatan wali.
3. **KPI belum dipublikasi:** tampilkan status “belum tersedia”, tanpa membocorkan nilai draft.
4. **Ganti anak/semester (Direncanakan):** kembali ke daftar anak; pilih semester yang tersedia untuk anak terkait.
5. **Pengumuman sekolah (Direncanakan):** daftar informasi sekolah yang ditujukan kepada orang tua.

Semua data anak harus diverifikasi lewat relasi `guardian_student` pada setiap permintaan.

## Siswa

**Menu utama:** Rapor Saya, Kehadiran, Jadwal (Direncanakan), Profil.

1. **Rapor Saya (Sudah ada: `student.home` mengarah ke `report.show`):** nilai per mapel, absensi, perilaku yang dibagikan, dan KPI jika pengaturan mengizinkan serta KPI sudah dipublikasikan.
2. **Kehadiran Saya (Direncanakan):** riwayat status per tanggal/mapel; absen mandiri hanya muncul jika fitur diaktifkan sekolah.
3. **Jadwal (Direncanakan):** kelas, mapel, guru, dan jadwal pembelajaran.
4. **Profil (Direncanakan):** informasi akun yang dapat dilihat dan perubahan profil yang diizinkan.

Siswa hanya dapat membuka profil dirinya sendiri. Skor KPI draft/ready tidak ditampilkan.

## Pola layar dan keadaan

- Setiap halaman menampilkan identitas peran dan semester aktif; menu menyesuaikan peran.
- Form memiliki keadaan memuat, kosong, berhasil, validasi gagal, dan tidak berwenang.
- Aksi simpan, hitung ulang, persetujuan, dan publikasi menampilkan konfirmasi/status yang jelas.
- Filter kelas/mapel/tanggal mempertahankan konteks saat kembali dari detail.
- Data sensitif dan tombol aksi harus dilindungi Policy/middleware/server, bukan sekadar disembunyikan dari menu.

## Status halaman saat ini

| Peran | Sudah tersedia | Yang utama perlu dikembangkan |
|---|---|---|
| Admin | KPI seluruh kelas, hitung dan publikasi | Panel Filament/data master, pengaturan, ringkasan, audit |
| Kepala sekolah | KPI seluruh kelas, hitung dan publikasi | Ringkasan sekolah, laporan, kehadiran guru |
| Guru | Beranda, penugasan kelas/mapel, daftar siswa wali kelas | Absensi, nilai, perilaku, check-in, alur edit dengan alasan |
| Orang tua | Daftar anak; route laporan tersedia tetapi view belum cocok | Perbaiki view laporan, filter semester, pengumuman |
| Siswa | Route laporan pribadi tersedia tetapi view belum cocok | Perbaiki view laporan, kehadiran, jadwal, profil |

