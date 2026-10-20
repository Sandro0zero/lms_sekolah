<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_resources', function (Blueprint $table) {
            $table->id();
            $table->enum('kind', ['material', 'library'])->index();
            $table->enum('library_type', ['ebook', 'article'])->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['kind', 'semester_id', 'class_id']);
            $table->index(['subject_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_resources');
    }
};
