<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('registration_date')->nullable()->after('birth_date');
        });

        // Untuk data lama, waktu pembuatan profil menjadi tanggal terdaftar yang tersedia.
        DB::table('students')->whereNull('registration_date')->update([
            'registration_date' => DB::raw('DATE(created_at)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('registration_date');
        });
    }
};
