<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('homework_submissions')) {
            Schema::create('homework_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->cascadeOnDelete();
                $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('original_filename');
                $table->timestamp('submitted_at');
                $table->string('status')->default('submitted');
                $table->decimal('grade', 6, 2)->nullable();
                $table->text('feedback')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['homework_assignment_id', 'student_user_id'], 'hw_submission_student_unique');
                $table->index(['homework_assignment_id', 'status'], 'hw_submission_status_idx');
            });
        } else {
            Schema::table('homework_submissions', function (Blueprint $table) {
                $table->unique(['homework_assignment_id', 'student_user_id'], 'hw_submission_student_unique');
                $table->index(['homework_assignment_id', 'status'], 'hw_submission_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};
