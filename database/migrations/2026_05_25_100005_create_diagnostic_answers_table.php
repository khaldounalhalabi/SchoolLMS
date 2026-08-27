<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('diagnostic_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('diagnostic_questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('question_options')->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_answers');
    }
};
