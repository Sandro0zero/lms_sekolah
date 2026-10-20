<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->text('address')->nullable()->after('photo_path');
            $table->string('rt', 5)->nullable()->after('address');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('kelurahan', 100)->nullable()->after('rw');
            $table->string('kecamatan', 100)->nullable()->after('kelurahan');
            $table->string('kota', 100)->nullable()->after('kecamatan');
            $table->string('phone', 20)->nullable()->after('kota');
            $table->string('contact_email')->nullable()->after('phone');
        });

        Schema::table('guardians', function (Blueprint $table): void {
            $table->string('contact_email')->nullable()->after('phone');
            $table->string('occupation', 100)->nullable()->after('contact_email');
            $table->decimal('monthly_income', 14, 2)->nullable()->after('occupation');
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table): void {
            $table->dropColumn(['contact_email', 'occupation', 'monthly_income']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn(['address', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota', 'phone', 'contact_email']);
        });
    }
};
