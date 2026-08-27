<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('grade_id')
                ->constrained('academic_years')
                ->nullOnDelete();
        });

        $academicYearId = DB::table('academic_years')
            ->where('is_active', true)
            ->value('id')
            ?? DB::table('academic_years')->orderBy('start_date')->value('id');

        if ($academicYearId) {
            DB::table('classrooms')
                ->whereNull('academic_year_id')
                ->update(['academic_year_id' => $academicYearId]);
        }

        Schema::table('classrooms', function (Blueprint $table) {
            $table->unique(
                ['academic_year_id', 'grade_id', 'name'],
                'classrooms_year_grade_name_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropUnique('classrooms_year_grade_name_unique');
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
