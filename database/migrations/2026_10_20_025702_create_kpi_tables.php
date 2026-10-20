<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KPI siswa per semester dan catatan wali kelas.
 *
 * `student_kpis` adalah SNAPSHOT: bobot dan skor disalin saat dihitung,
 * sehingga mengubah pengaturan nanti tidak mengubah KPI semester lama.
 * Semua skor komponen dinormalisasi ke skala 0-100.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();

            // Skor komponen (0-100)
            $table->decimal('grade_score', 5, 2)->nullable();
            $table->decimal('attendance_score', 5, 2)->nullable();
            $table->decimal('behavior_score', 5, 2)->nullable();

            // Bobot yang dipakai saat dihitung (salinan dari settings)
            $table->unsignedTinyInteger('weight_grade');
            $table->unsignedTinyInteger('weight_attendance');
            $table->unsignedTinyInteger('weight_behavior');

            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('category', 50)->nullable();            // salinan nama kategori saat itu
            $table->decimal('previous_final_score', 5, 2)->nullable(); // semester sebelumnya, untuk perbandingan
            $table->json('breakdown')->nullable();                 // nilai akhir per mapel, jumlah hadir/sakit/izin/alfa/bolos

            // Alur: draft (dihitung) -> ready (diusulkan wali kelas) -> published (oleh kepsek/admin)
            $table->enum('status', ['draft', 'ready', 'published'])->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('ready_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ready_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable(); // orang tua & siswa hanya melihat jika terisi
            $table->timestamps();

            $table->unique(['student_id', 'semester_id']);
            $table->index(['class_id', 'semester_id', 'status']);
        });

        // Catatan umum wali kelas, tampil ke orang tua bersama KPI
        Schema::create('homeroom_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
            $table->unique(['semester_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeroom_notes');
        Schema::dropIfExists('student_kpis');
    }
};