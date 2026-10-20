<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data master: pengguna, semester, guru, kelas, mapel, siswa, wali murid,
 * pendaftaran siswa ke kelas, dan penugasan guru.
 *
 * CATATAN: ganti migrasi `users` bawaan Laravel dengan yang ini.
 * Tabel wali murid bernama `guardians` (bukan `parents`) karena
 * `Parent` adalah kata terlarang di PHP untuk nama class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password')->nullable(); // login ditunda, jadi boleh kosong dulu
            $table->enum('role', ['admin', 'kepsek', 'guru', 'siswa', 'orangtua']);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index('role');
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9);          // contoh: 2026/2027
            $table->unsignedTinyInteger('term');         // 1 = Ganjil, 2 = Genap
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false); // hanya satu yang aktif (dijaga di aplikasi)
            $table->timestamps();
            $table->unique(['academic_year', 'term']);
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);                  // contoh: 8A
            $table->unsignedTinyInteger('grade_level');  // tingkat: 7, 8, 9, dst.
            $table->string('academic_year', 9);
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();
            $table->unique(['name', 'academic_year']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // Nullable: tidak semua siswa punya HP/akun sendiri
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->unique();
            $table->string('name');
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'pindah', 'keluar'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Satu wali bisa punya banyak anak, satu anak bisa punya lebih dari satu wali
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('guardians')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship', 30)->nullable(); // ayah, ibu, wali
            $table->timestamps();
            $table->unique(['guardian_id', 'student_id']);
        });

        // Siswa berada di satu kelas per tahun ajaran
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('academic_year', 9); // salinan dari kelas, untuk menjamin keunikan
            $table->timestamps();
            $table->unique(['student_id', 'academic_year']);
            $table->index('class_id');
        });

        // Guru mengajar mapel X di kelas Y pada semester Z
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['class_id', 'subject_id', 'semester_id']); // satu guru per mapel per kelas
            $table->index(['teacher_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('guardian_student');
        Schema::dropIfExists('guardians');
        Schema::dropIfExists('students');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('users');
    }
};