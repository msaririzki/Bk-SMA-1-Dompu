<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nip')->nullable()->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->boolean('is_counselor')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('temporary_id')->unique();
            $table->string('nis')->nullable()->unique();
            $table->string('nisn')->nullable()->unique();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('gender', 1);
            $table->string('status', 24)->default('active')->index();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('student_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->string('source')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('grade_level', 8)->index();
            $table->string('track', 32)->nullable();
            $table->unsignedSmallInteger('group_number')->nullable();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();
            $table->unique(['academic_year_id', 'name']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('roll_number')->nullable();
            $table->string('status', 24)->default('active');
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id']);
        });

        Schema::create('counselor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselor_assignments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('student_aliases');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
    }
};
