<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('homework_assignments')) {
            Schema::create('homework_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_assignment_id')->constrained('teacher_subject_classroom')->cascadeOnDelete();
                $table->foreignId('teacher_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('due_date');
                $table->decimal('max_score', 6, 2)->default(100);
                $table->string('attachment_path')->nullable();
                $table->string('attachment_original_name')->nullable();
                $table->timestamps();

                $table->index(['classroom_id', 'academic_year_id', 'due_date'], 'hw_classroom_year_due_idx');
                $table->index(['teacher_user_id', 'created_at'], 'hw_teacher_created_idx');
            });
        } else {
            Schema::table('homework_assignments', function (Blueprint $table) {
                $table->index(['classroom_id', 'academic_year_id', 'due_date'], 'hw_classroom_year_due_idx');
                $table->index(['teacher_user_id', 'created_at'], 'hw_teacher_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
    }
};
