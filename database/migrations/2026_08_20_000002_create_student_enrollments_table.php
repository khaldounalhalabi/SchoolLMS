<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_enrollments')) {
            Schema::create('student_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->date('enrollment_date');
                $table->date('withdrawal_date')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();

                $table->index(['academic_year_id', 'classroom_id', 'status'], 'enrollments_classroom_status_idx');
                $table->index(['student_user_id', 'academic_year_id', 'status'], 'enrollments_student_status_idx');
            });
        } else {
            Schema::table('student_enrollments', function (Blueprint $table) {
                $table->index(['student_user_id', 'academic_year_id', 'status'], 'enrollments_student_status_idx');
            });
        }

        DB::table('student_profiles')
            ->join('classrooms', 'classrooms.id', '=', 'student_profiles.classroom_id')
            ->whereNotNull('classrooms.academic_year_id')
            ->select([
                'student_profiles.user_id',
                'classrooms.academic_year_id',
                'student_profiles.classroom_id',
                'student_profiles.enrollment_date',
            ])
            ->orderBy('student_profiles.id')
            ->each(function (object $profile): void {
                $alreadyEnrolled = DB::table('student_enrollments')
                    ->where('student_user_id', $profile->user_id)
                    ->where('academic_year_id', $profile->academic_year_id)
                    ->exists();

                if (! $alreadyEnrolled) {
                    DB::table('student_enrollments')->insert([
                        'student_user_id' => $profile->user_id,
                        'academic_year_id' => $profile->academic_year_id,
                        'classroom_id' => $profile->classroom_id,
                        'enrollment_date' => $profile->enrollment_date,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
