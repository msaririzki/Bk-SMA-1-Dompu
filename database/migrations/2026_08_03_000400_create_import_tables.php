<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('file_hash', 64);
            $table->string('status', 24)->default('review');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('ready_rows')->default(0);
            $table->unsignedInteger('conflict_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->unsignedInteger('imported_rows')->default(0);
            $table->timestamp('committed_at')->nullable();
            $table->timestamps();
            $table->unique(['academic_year_id', 'file_hash']);
        });

        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('batch_id')->constrained('import_batches')->cascadeOnDelete();
            $table->string('sheet_name');
            $table->unsignedInteger('row_number');
            $table->json('raw_payload');
            $table->json('normalized_payload')->nullable();
            $table->string('status', 24);
            $table->foreignUuid('matched_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('message')->nullable();
            $table->timestamps();
            $table->unique(['batch_id', 'sheet_name', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('import_batches');
    }
};
