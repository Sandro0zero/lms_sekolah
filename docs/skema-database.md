# Skema database LMS Sekolah

Skema target untuk implementasi Laravel + MySQL. Nama tabel aktual diselaraskan dengan model aplikasi: `school_classes` (kelas), `guardians` (orang tua), dan `homeroom_notes` (catatan wali kelas).

## Konvensi

- MySQL dengan `utf8mb4` dan `utf8mb4_unicode_ci`.
- Primary key `BIGINT UNSIGNED AUTO_INCREMENT` (`$table->id()`); foreign key `BIGINT UNSIGNED` (`foreignId`).
- Skor memakai `DECIMAL(5,2)`. Semua tabel domain memiliki `created_at` dan `updated_at`, kecuali `audit_logs` yang hanya memiliki `created_at`. Profil tertentu memakai `deleted_at`.
- FK operasional umumnya cascade. FK aktor/pemeriksa memakai `ON DELETE SET NULL` untuk menjaga riwayat.

## Akun dan data master

| Tabel | Kolom inti | Constraint dan relasi |
|---|---|---|
| `users` | `name VARCHAR(255)`; `username VARCHAR(255)`; `email VARCHAR(255) NULL`; `phone VARCHAR(20) NULL`; `password VARCHAR(255) NULL`; `role ENUM(admin,kepsek,guru,siswa,orangtua)`; `is_active BOOLEAN DEFAULT true`; remember token; timestamps; soft delete | PK `id`; username unik; email unik nullable; indeks role. Password sementara nullable untuk login demo. |
| `semesters` | `academic_year VARCHAR(9)`; `term TINYINT UNSIGNED`; `start_date DATE`; `end_date DATE`; `is_active BOOLEAN DEFAULT false`; timestamps | PK `id`; unik (`academic_year`,`term`). Satu semester aktif ditegakkan aplikasi. |
| `teachers` | `user_id BIGINT UNSIGNED NULL`; `nip VARCHAR(30) NULL`; `name VARCHAR(255)`; `phone VARCHAR(20) NULL`; `is_active BOOLEAN`; timestamps; soft delete | PK `id`; user FK SET NULL; NIP unik. |
| `school_classes` | `name VARCHAR(30)`; `grade_level TINYINT UNSIGNED`; `academic_year VARCHAR(9)`; `homeroom_teacher_id BIGINT UNSIGNED NULL`; timestamps | PK `id`; wali kelas FK teachers SET NULL; unik (`name`,`academic_year`). |
| `subjects` | `code VARCHAR(20) NULL`; `name VARCHAR(255)`; `is_active BOOLEAN`; timestamps | PK `id`; code unik nullable. |
| `students` | `user_id BIGINT UNSIGNED NULL`; `nis VARCHAR(30)`; `nisn VARCHAR(30) NULL`; `name VARCHAR(255)`; `gender ENUM(L,P)`; `birth_date DATE NULL`; `status ENUM(aktif,lulus,pindah,keluar)`; timestamps; soft delete | PK `id`; user FK SET NULL; NIS dan NISN unik. |
| `guardians` | `user_id BIGINT UNSIGNED NULL`; `name VARCHAR(255)`; `phone VARCHAR(20) NULL`; timestamps; soft delete | PK `id`; user FK SET NULL. |
| `guardian_student` | `guardian_id BIGINT UNSIGNED`; `student_id BIGINT UNSIGNED`; `relationship VARCHAR(30) NULL`; timestamps | PK `id`; FK ke guardians/students cascade; unik pasangan guardian-student. Banyak wali dapat terhubung ke banyak anak. |
| `enrollments` | `student_id BIGINT UNSIGNED`; `class_id BIGINT UNSIGNED`; `academic_year VARCHAR(9)`; timestamps | PK `id`; FK ke students/school_classes cascade; unik (`student_id`,`academic_year`); indeks class_id. |
| `teaching_assignments` | `teacher_id`, `class_id`, `subject_id`, `semester_id BIGINT UNSIGNED`; timestamps | PK `id`; semua FK cascade; unik (`class_id`,`subject_id`,`semester_id`); indeks (`teacher_id`,`semester_id`). |

## Nilai dan absensi

| Tabel | Kolom inti | Constraint dan relasi |
|---|---|---|
| `grade_weights` | `semester_id`, `subject_id BIGINT UNSIGNED`; `weight_uh`, `weight_uts`, `weight_uas TINYINT UNSIGNED`; `kkm DECIMAL(5,2) DEFAULT 75`; timestamps | PK `id`; FK semester/mapel; unik semester-mapel; jumlah bobot harus 100 (CHECK dan validasi aplikasi). |
| `grades` | FK `student_id`, `class_id`, `subject_id`, `semester_id`; `type ENUM(uh,uts,uas)`; `sequence TINYINT UNSIGNED DEFAULT 1`; `title VARCHAR(255) NULL`; `score DECIMAL(5,2)`; `is_remedial BOOLEAN`; `original_score DECIMAL(5,2) NULL`; `assessed_on DATE NULL`; `input_by BIGINT UNSIGNED`; `updated_by BIGINT UNSIGNED NULL`; timestamps | PK `id`; FK master; input_by → users; updated_by → users SET NULL; unik (student, subject, semester, type, sequence); indeks kelas-mapel-semester dan siswa-semester. Jendela edit dimulai dari created_at yang tidak berubah. |
| `student_attendances` | FK `student_id`, `class_id`, `subject_id`, `semester_id`; `date DATE`; `lesson_period TINYINT UNSIGNED DEFAULT 1`; `status ENUM(hadir,sakit,izin,alfa,bolos)`; `note VARCHAR(255) NULL`; `source ENUM(guru,admin,siswa)`; `recorded_by BIGINT UNSIGNED`; `updated_by BIGINT UNSIGNED NULL`; timestamps | PK `id`; FK master; updated_by SET NULL; unik (student, subject, date, lesson_period); indeks (class_id,date) dan (student_id,semester_id,status). |
| `teacher_leave_requests` | `teacher_id BIGINT UNSIGNED`; `date_from`, `date_to DATE`; `type ENUM(sakit,izin,dinas)`; `reason TEXT`; `status ENUM(pending,approved,rejected)`; `reviewed_by BIGINT UNSIGNED NULL`; `reviewed_at TIMESTAMP NULL`; `review_note VARCHAR(255) NULL`; timestamps | PK `id`; FK teacher; reviewer → users SET NULL; indeks teacher-status. |
| `teacher_attendances` | `teacher_id BIGINT UNSIGNED`; `date DATE`; `check_in_at`, `check_out_at TIMESTAMP NULL`; `status ENUM(hadir,terlambat,sakit,izin,dinas,alfa)`; `note VARCHAR(255) NULL`; `source ENUM(self,admin,leave)`; `leave_request_id`, `recorded_by BIGINT UNSIGNED NULL`; timestamps | PK `id`; FK teacher; leave request dan recorded_by SET NULL; unik (teacher,date); indeks date. |

## Pengaturan, perilaku, dan KPI

| Tabel | Kolom inti | Constraint dan relasi |
|---|---|---|
| `settings` | `key VARCHAR(255)`; `value JSON`; `description VARCHAR(255) NULL`; `updated_by BIGINT UNSIGNED NULL`; timestamps | PK `id`; key unik; updated_by → users SET NULL. Menampung batas edit, bobot KPI, dampak status absensi, skala perilaku. |
| `kpi_categories` | `name VARCHAR(50)`; `min_score`, `max_score DECIMAL(5,2)`; `sort_order TINYINT UNSIGNED`; timestamps | PK `id`; validasi aplikasi mencegah rentang tumpang tindih. |
| `behavior_aspects` | `name VARCHAR(255)`; `sort_order TINYINT UNSIGNED`; `is_active BOOLEAN`; timestamps | PK `id`. |
| `behavior_periods` | `semester_id BIGINT UNSIGNED`; `opens_at`, `closes_at DATETIME`; `is_locked BOOLEAN`; timestamps | PK `id`; semester FK; semester_id unik; validasi opens_at < closes_at. |
| `behavior_assessments` | `semester_id`, `student_id`, `teacher_id BIGINT UNSIGNED`; `note TEXT NULL`; `submitted_at TIMESTAMP NULL`; timestamps | PK `id`; FK semester/siswa/guru; unik (semester, student, teacher). |
| `behavior_scores` | `assessment_id`, `aspect_id BIGINT UNSIGNED`; `score TINYINT UNSIGNED` | PK `id`; assessment FK cascade; aspect FK restrict; unik (assessment, aspect); skor 1..skala divalidasi aplikasi. |
| `student_kpis` | FK `student_id`, `semester_id`, `class_id`; `grade_score`, `attendance_score`, `behavior_score DECIMAL(5,2) NULL`; bobot komponen `TINYINT UNSIGNED`; `final_score DECIMAL(5,2) NULL`; `category VARCHAR(50) NULL`; `previous_final_score DECIMAL(5,2) NULL`; `breakdown JSON NULL`; `status ENUM(draft,ready,published)`; `calculated_at TIMESTAMP NULL`; `ready_by`, `published_by BIGINT UNSIGNED NULL`; `ready_at`, `published_at TIMESTAMP NULL`; timestamps | PK `id`; unik (student,semester); indeks (class,semester,status); aktor FK users SET NULL. Skor/bobot snapshot. Siswa/orang tua hanya melihat status published dan published_at terisi. |
| `homeroom_notes` | `semester_id`, `student_id`, `teacher_id BIGINT UNSIGNED`; `note TEXT`; timestamps | PK `id`; FK semester/siswa/guru; unik (semester,student). |

## Audit dan tabel framework

Target `audit_logs`: `id`; `user_id BIGINT UNSIGNED NULL`; `event VARCHAR(50)`; `auditable_type VARCHAR(255)`; `auditable_id BIGINT UNSIGNED`; `old_values JSON NULL`; `new_values JSON NULL`; `reason TEXT NULL`; `ip_address VARCHAR(45) NULL`; `created_at TIMESTAMP`. FK user SET NULL; indeks (auditable_type,auditable_id), (user_id,created_at), dan created_at. Relasi auditable polimorfik; audit tidak ikut terhapus saat record induk dihapus.

**Koreksi penting:** migrasi `create_audit_logs_tables` saat ini membuat tabel kosong `audit_logs_tables`, sedangkan `AuditLog`/`AuditLogger` membutuhkan tabel `audit_logs` dengan kolom target di atas. Buat migrasi koreksi baru jika migrasi lama pernah diterapkan di database bersama; jangan mengubah riwayat yang sudah diterapkan.

Laravel juga menggunakan `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, dan `failed_jobs` sesuai driver sesi/cache/queue.

## Aturan integritas aplikasi

1. Data guru dibatasi melalui `teaching_assignments`; data orang tua dibatasi melalui `guardian_student`.
2. Edit nilai/absensi guru maksimal 7 hari dari input pertama. Setelah itu hanya admin dengan alasan wajib dan audit log.
3. Validasi server wajib memeriksa rentang skor, tanggal, status absensi, total bobot, dan hubungan kelas-mapel-semester.
4. Hitung/publikasi KPI dalam transaksi; snapshot skor/bobot lama tidak berubah karena pengaturan baru.
5. Constraint unik di database melindungi dari request bersamaan; validasi form saja tidak cukup.

## Penyesuaian dari rancangan awal

- `parents` menggunakan nama aktual `guardians`; `classes` menjadi `school_classes`; `class_notes` menjadi `homeroom_notes`.
- Absensi siswa dicatat per mapel/pertemuan. Proyek juga memiliki absensi dan pengajuan izin guru.
