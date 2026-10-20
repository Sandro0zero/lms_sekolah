<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan yang bisa diubah admin: pengaturan umum, bobot nilai + KKM per mapel,
 * dan kategori KPI.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pengaturan umum (batas hari ubah, bobot KPI, dampak status absensi, dll.)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // contoh: kpi.weights
            $table->json('value');
            $table->string('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Bobot UH/UTS/UAS dan KKM per mapel per semester
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedTinyInteger('weight_uh');
            $table->unsignedTinyInteger('weight_uts');
            $table->unsignedTinyInteger('weight_uas');
            $table->decimal('kkm', 5, 2)->default(75);
            $table->timestamps();
            $table->unique(['semester_id', 'subject_id']);
        });

        // Total bobot wajib 100 (didukung MySQL 8.0.16+ / MariaDB 10.2+)
        DB::statement('ALTER TABLE grade_weights ADD CONSTRAINT chk_grade_weights_total CHECK (weight_uh + weight_uts + weight_uas = 100)');

        Schema::create('kpi_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_categories');
        Schema::dropIfExists('grade_weights');
        Schema::dropIfExists('settings');
    }
};