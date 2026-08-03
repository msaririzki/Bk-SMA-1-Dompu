<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('violation_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('violation_categories')->restrictOnDelete();
            $table->string('code')->unique();
            $table->text('name');
            $table->unsignedSmallInteger('points');
            $table->text('sanction')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('severity_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('min_points');
            $table->unsignedSmallInteger('max_points')->nullable();
            $table->string('color', 24)->default('slate');
            $table->string('recommended_action')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('violation_cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('case_number')->unique();
            $table->foreignUuid('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->dateTime('occurred_at')->index();
            $table->string('location')->nullable();
            $table->text('chronology');
            $table->string('status', 24)->default('open')->index();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('case_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('case_id')->constrained('violation_cases')->cascadeOnDelete();
            $table->foreignId('instrument_id')->nullable()->constrained('violation_instruments')->nullOnDelete();
            $table->string('instrument_code');
            $table->text('instrument_name');
            $table->unsignedSmallInteger('points');
            $table->text('sanction_snapshot')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('case_id')->constrained('violation_cases')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('type', 32);
            $table->date('scheduled_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_contact')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('planned');
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('case_id')->nullable()->constrained('violation_cases')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('case_items');
        Schema::dropIfExists('violation_cases');
        Schema::dropIfExists('severity_levels');
        Schema::dropIfExists('violation_instruments');
        Schema::dropIfExists('violation_categories');
    }
};
