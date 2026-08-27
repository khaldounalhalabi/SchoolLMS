<?php

use App\Enums\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuition_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default(Currency::USD->value);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuition_fees');
    }
};
