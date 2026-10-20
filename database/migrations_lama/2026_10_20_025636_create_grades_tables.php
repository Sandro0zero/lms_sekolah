<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nilai mentah per siswa per mapel. Nilai akhir berbobot TIDAK disimpan di sini,
 * melainkan dihitung dari baris ini + tabel `grade_weights`.
 * Tugas dicatat sebagai salah satu ulangan harian (UH) untuk tahap 1.
 *
 * Batas ubah 7 hari dihitung dari `created_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->enum('type', ['uh', 'uts', 'uas']);
            $table->unsignedTinyInteger('sequence')->default(1); // UH ke-1, ke-2, ...; UTS/UAS selalu 1
            $table->string('title')->nullable();                  // contoh: "UH Bab 2"
            $table->decimal('score', 5, 2);                       // 0-100, divalidasi di aplikasi
            $table->boolean('is_remedial')->default(false);
            $table->decimal('original_score', 5, 2)->nullable();  // nilai sebelum remedial
            $table->date('assessed_on')->nullable();              // tanggal ulangan dilaksanakan
            $table->foreignId('input_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'semester_id', 'type', 'sequence'], 'uq_grade_entry');
            $table->index(['class_id', 'subject_id', 'semester_id']);
            $table->index(['student_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};