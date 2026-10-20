<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->string('birth_place', 100)->nullable()->after('birth_date');
            $table->string('religion', 50)->nullable()->after('birth_place');
            $table->string('nik', 16)->nullable()->unique()->after('religion');
            $table->string('photo_path')->nullable()->after('nik');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropUnique(['nik']);
            $table->dropColumn(['birth_place', 'religion', 'nik', 'photo_path']);
        });
    }
};
