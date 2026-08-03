<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('case_id')->nullable()->constrained('violation_cases')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('type', 40);
            $table->string('number')->nullable();
            $table->date('document_date');
            $table->string('status', 24)->default('draft');
            $table->json('payload');
            $table->timestamps();
        });

        Schema::create('home_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_id')->unique()->constrained('documents')->cascadeOnDelete();
            $table->string('counselee_name');
            $table->string('class_name');
            $table->string('gender', 1);
            $table->text('address')->nullable();
            $table->string('parent_name')->nullable();
            $table->text('problem');
            $table->text('purpose');
            $table->date('visit_date');
            $table->string('met_with')->nullable();
            $table->text('result');
            $table->text('follow_up');
            $table->string('counselor_name');
            $table->string('counselor_nip')->nullable();
            $table->string('homeroom_name');
            $table->string('homeroom_nip')->nullable();
            $table->string('coordinator_name');
            $table->string('coordinator_nip')->nullable();
            $table->string('place')->default('Dompu');
            $table->timestamps();
        });

        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->boolean('is_published')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 24)->default('text');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('home_visits');
        Schema::dropIfExists('documents');
    }
};
