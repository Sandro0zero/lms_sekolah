<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penilaian perilaku siswa oleh guru menjelang akhir semester.
 * Aspek dan periode diatur admin. Rata-rata dari semua guru menjadi
 * komponen perilaku pada KPI siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('behavior_aspects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Periode buka-tutup penilaian per semester
        Schema::create('behavior_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->unique()->constrained('semesters')->cascadeOnDelete();
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->boolean('is_locked')->default(false); // terkunci setelah ditutup
            $table->timestamps();
        });

        // Satu penilaian: satu guru menilai satu siswa pada satu semester
        Schema::create('behavior_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->text('note')->nullable();          // catatan internal, tidak tampil ke orang tua
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique(['semester_id', 'student_id', 'teacher_id'], 'uq_behavior_assessment');
        });

        // Skor per aspek (skala diatur di settings, awalnya 1-4)
        Schema::create('behavior_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('behavior_assessments')->cascadeOnDelete();
            $table->foreignId('aspect_id')->constrained('behavior_aspects')->restrictOnDelete();
            $table->unsignedTinyInteger('score');
            $table->unique(['assessment_id', 'aspect_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('behavior_scores');
        Schema::dropIfExists('behavior_assessments');
        Schema::dropIfExists('behavior_periods');
        Schema::dropIfExists('behavior_aspects');
    }
};