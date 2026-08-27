<?php

use App\Enums\AbsenceJustificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->unique()->constrained('attendance')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->string('document_path')->nullable();
            $table->enum('status', AbsenceJustificationStatus::values())->default(AbsenceJustificationStatus::PENDING->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_justifications');
    }
};
