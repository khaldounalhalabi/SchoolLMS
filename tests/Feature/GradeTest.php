<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GradeTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $student;

    private Subject $subject;

    private ExamType $examType;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);
        $semester = Semester::create([
            'academic_year_id' => $year->id,
            'name' => 'Fall',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'is_active' => true,
        ]);
        $grade = Grade::create([
            'school_id' => $school->id,
            'name' => 'Grade 8',
            'order_index' => 1,
        ]);
        $classroom = Classroom::create([
            'grade_id' => $grade->id,
            'name' => '8-A',
            'capacity' => 30,
        ]);
        $this->subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Math',
            'code' => 'MATH',
        ]);
        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->student = User::factory()->create(['role' => 'student']);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => '2025-09-01',
        ]);
        TeacherSubjectClassroom::create([
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
        ]);
        $this->examType = ExamType::create([
            'semester_id' => $semester->id,
            'name' => 'Midterm',
            'weight_percent' => 40,
        ]);
    }

    public function test_teacher_can_store_a_grade_for_an_enrolled_student(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/grades/bulk', [
            'grades' => [[
                'student_id' => $this->student->id,
                'subject_id' => $this->subject->id,
                'exam_type_id' => $this->examType->id,
                'score' => 18,
                'max_score' => 20,
            ]],
        ])->assertCreated()
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('student_grades', [
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'exam_type_id' => $this->examType->id,
            'score' => 18,
        ]);
    }

    public function test_teacher_cannot_store_a_grade_for_an_unassigned_student(): void
    {
        $outsideStudent = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/grades/bulk', [
            'grades' => [[
                'student_id' => $outsideStudent->id,
                'subject_id' => $this->subject->id,
                'exam_type_id' => $this->examType->id,
                'score' => 18,
                'max_score' => 20,
            ]],
        ])->assertForbidden();

        $this->assertDatabaseCount('student_grades', 0);
    }

    public function test_score_cannot_exceed_max_score(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/grades/bulk', [
            'grades' => [[
                'student_id' => $this->student->id,
                'subject_id' => $this->subject->id,
                'exam_type_id' => $this->examType->id,
                'score' => 21,
                'max_score' => 20,
            ]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('student_grades', 0);
    }

    public function test_teacher_web_grade_entry_uses_the_shared_grade_service(): void
    {
        $this->actingAs($this->teacher)
            ->from(route('teacher.grades.entry'))
            ->post(route('teacher.grades.store'), [
                'subject_id' => $this->subject->id,
                'exam_type_id' => $this->examType->id,
                'max_score' => 20,
                'scores' => [$this->student->id => 17],
            ])
            ->assertRedirect(route('teacher.grades.entry'))
            ->assertSessionHas('success', 'Grades saved successfully.');

        $this->assertDatabaseHas('student_grades', [
            'student_user_id' => $this->student->id,
            'score' => 17,
        ]);
    }

    public function test_shared_report_card_flow_serves_api_and_student_web(): void
    {
        Sanctum::actingAs($this->teacher);
        $this->postJson('/api/v1/grades/bulk', [
            'grades' => [[
                'student_id' => $this->student->id,
                'subject_id' => $this->subject->id,
                'exam_type_id' => $this->examType->id,
                'score' => 18,
                'max_score' => 20,
            ]],
        ])->assertCreated();

        $this->getJson('/api/v1/students/'.$this->student->id.'/report-card?semester_id='.$this->examType->semester_id)
            ->assertOk()
            ->assertJsonPath('data.student.id', $this->student->id);

        $this->actingAs($this->student)
            ->get(route('student.results', ['semester_id' => $this->examType->semester_id]))
            ->assertOk()
            ->assertSee('Math');
    }
}
