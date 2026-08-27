<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_map_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained()->cascadeOnDelete();
            $table->decimal('mastery_percent', 5, 2)->default(0);
            $table->timestamp('last_assessed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_user_id', 'learning_objective_id'], 'unique_student_objective');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_map_results');
    }
};
