<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->decimal('weighted_average', 5, 2)->nullable();
            $table->string('letter_grade', 2)->nullable();
            $table->timestamps();

            $table->unique(['student_user_id', 'subject_id', 'semester_id'], 'unique_summary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_summaries');
    }
};
