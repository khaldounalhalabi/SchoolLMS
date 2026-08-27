<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeSummary;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentGrade;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $student;

    private Semester $semester;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
        $this->semester = Semester::create([
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
        $this->admin = User::factory()->create(['role' => 'admin']);
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
        $examType = ExamType::create([
            'semester_id' => $this->semester->id,
            'name' => 'Midterm',
            'weight_percent' => 100,
        ]);
        StudentGrade::create([
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'exam_type_id' => $examType->id,
            'semester_id' => $this->semester->id,
            'teacher_user_id' => $this->teacher->id,
            'score' => 17,
            'max_score' => 20,
        ]);
        GradeSummary::create([
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $this->semester->id,
            'weighted_average' => 85,
            'letter_grade' => 'B',
        ]);

        foreach (['present', 'present', 'late', 'absent', 'excused'] as $index => $status) {
            Attendance::create([
                'student_user_id' => $this->student->id,
                'classroom_id' => $classroom->id,
                'date' => '2025-10-'.str_pad((string) (10 + $index), 2, '0', STR_PAD_LEFT),
                'status' => $status,
                'recorded_by' => $this->teacher->id,
            ]);
        }
    }

    public function test_admin_can_view_filtered_reports_with_summary_metrics(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.index', [
            'academic_year_id' => $this->semester->academic_year_id,
            'semester_id' => $this->semester->id,
        ]));

        $response->assertOk()
            ->assertSee('Reports & Analytics')
            ->assertSee('85%')
            ->assertSee('100%')
            ->assertSee('75%')
            ->assertSee($this->student->name)
            ->assertSee('Math');
    }

    public function test_non_admin_users_cannot_view_reports(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    public function test_reports_can_be_rendered_in_arabic(): void
    {
        app()->setLocale('ar');

        $this->actingAs($this->admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('التقارير والتحليلات')
            ->assertSee('مرشحات التقرير');
    }

    public function test_admin_can_export_filtered_student_rows(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.reports.export', [
            'semester_id' => $this->semester->id,
        ]));

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString($this->student->name, $response->streamedContent());
        $this->assertStringContainsString('Average Score', $response->streamedContent());
    }
}
