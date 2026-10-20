<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Absensi siswa (per mapel per pertemuan, diisi guru) dan absensi guru.
 *
 * Batas ubah 7 hari dihitung dari `created_at` (waktu input pertama) dan
 * ditegakkan di Policy, dengan angka hari dari tabel `settings`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedTinyInteger('lesson_period')->default(1); // jam/pertemuan ke-n pada hari itu
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alfa', 'bolos'])->default('hadir');
            $table->string('note')->nullable(); // wajib untuk selain hadir (divalidasi di aplikasi)
            $table->enum('source', ['guru', 'admin', 'siswa'])->default('guru'); // 'siswa' disiapkan untuk absen mandiri nanti
            $table->foreignId('recorded_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Mencegah absensi ganda
            $table->unique(['student_id', 'subject_id', 'date', 'lesson_period'], 'uq_student_attendance');
            $table->index(['class_id', 'date']);
            $table->index(['student_id', 'semester_id', 'status']);
        });

        Schema::create('teacher_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('type', ['sakit', 'izin', 'dinas']);
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete(); // kepsek
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->enum('status', ['hadir', 'terlambat', 'sakit', 'izin', 'dinas', 'alfa'])->default('hadir');
            $table->string('note')->nullable();
            $table->enum('source', ['self', 'admin', 'leave'])->default('self'); // check-in sendiri, koreksi admin, atau dari izin disetujui
            $table->foreignId('leave_request_id')->nullable()->constrained('teacher_leave_requests')->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'date']); // satu catatan per guru per hari
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendances');
        Schema::dropIfExists('teacher_leave_requests');
        Schema::dropIfExists('student_attendances');
    }
};