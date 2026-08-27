<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_be_assigned_to_a_different_subject(): void
    {
        $school = School::create(['name' => 'Test School']);
        $academicYear = AcademicYear::create([
            'school_id'  => $school->id,
            'name'       => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date'   => '2026-06-30',
        ]);
        $grade = Grade::create([
            'school_id'   => $school->id,
            'name'        => 'Grade 10',
            'order_index' => 1,
        ]);
        $classroom = Classroom::create([
            'grade_id' => $grade->id,
            'name'     => '10-A',
            'capacity' => 30,
        ]);
        $history = Subject::create([
            'school_id' => $school->id,
            'name'      => 'History',
            'code'      => 'HIST101',
        ]);
        $mathematics = Subject::create([
            'school_id' => $school->id,
            'name'      => 'Mathematics',
            'code'      => 'MATH101',
        ]);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        TeacherSubjectClassroom::create([
            'teacher_user_id' => $teacher->id,
            'subject_id'      => $history->id,
            'classroom_id'    => $classroom->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $response = $this->actingAs($admin)->from(route('admin.assignments.create'))->post(
            route('admin.assignments.store'),
            [
                'teacher_user_id' => $teacher->id,
                'subject_id'      => $mathematics->id,
                'classroom_id'    => $classroom->id,
                'academic_year_id' => $academicYear->id,
            ]
        );

        $response->assertRedirect(route('admin.assignments.create'))
            ->assertSessionHasErrors('subject_id');

        $this->assertDatabaseMissing('teacher_subject_classroom', [
            'teacher_user_id' => $teacher->id,
            'subject_id'      => $mathematics->id,
        ]);
    }
}
