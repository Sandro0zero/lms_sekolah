<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original migration accidentally created `audit_logs_tables` with only timestamps.
        if (Schema::hasTable('audit_logs_tables') && ! Schema::hasTable('audit_logs')) {
            Schema::rename('audit_logs_tables', 'audit_logs');
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event');
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('reason')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['auditable_type', 'auditable_id']);
            });

            return;
        }

        $missing = [];
        foreach ([
            'user_id' => fn (Blueprint $table) => $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(),
            'event' => fn (Blueprint $table) => $table->string('event'),
            'auditable_type' => fn (Blueprint $table) => $table->string('auditable_type'),
            'auditable_id' => fn (Blueprint $table) => $table->unsignedBigInteger('auditable_id'),
            'old_values' => fn (Blueprint $table) => $table->json('old_values')->nullable(),
            'new_values' => fn (Blueprint $table) => $table->json('new_values')->nullable(),
            'reason' => fn (Blueprint $table) => $table->text('reason')->nullable(),
            'ip_address' => fn (Blueprint $table) => $table->string('ip_address', 45)->nullable(),
        ] as $column => $definition) {
            if (! Schema::hasColumn('audit_logs', $column)) {
                $missing[$column] = $definition;
            }
        }

        if ($missing !== []) {
            Schema::table('audit_logs', function (Blueprint $table) use ($missing): void {
                foreach ($missing as $definition) {
                    $definition($table);
                }
            });
        }

        if (! Schema::hasColumn('audit_logs', 'created_at')) {
            Schema::table('audit_logs', fn (Blueprint $table) => $table->timestamp('created_at')->nullable());
        }

        if (Schema::hasColumn('audit_logs', 'updated_at')) {
            Schema::table('audit_logs', fn (Blueprint $table) => $table->dropColumn('updated_at'));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
