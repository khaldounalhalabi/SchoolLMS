<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\School;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeworkTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;

    private User $student;

    private HomeworkAssignment $homework;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $school = School::create(['name' => 'Homework School']);
        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);
        $grade = Grade::create([
            'school_id' => $school->id,
            'name' => 'Grade 7',
            'order_index' => 1,
        ]);
        $classroom = Classroom::create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
            'name' => '7-A',
            'capacity' => 30,
        ]);
        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);
        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->student = User::factory()->create(['role' => 'student']);

        $assignment = TeacherSubjectClassroom::create([
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
        ]);
        StudentEnrollment::create([
            'student_user_id' => $this->student->id,
            'academic_year_id' => $year->id,
            'classroom_id' => $classroom->id,
            'enrollment_date' => '2025-09-01',
            'status' => 'active',
        ]);

        $this->homework = HomeworkAssignment::create([
            'teacher_assignment_id' => $assignment->id,
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $subject->id,
            'classroom_id' => $classroom->id,
            'academic_year_id' => $year->id,
            'title' => 'Algebra Exercises',
            'description' => 'Complete exercises 1 to 10.',
            'due_date' => now()->addDay()->toDateString(),
            'max_score' => 20,
        ]);
    }

    public function test_teacher_can_create_homework_with_reference_file(): void
    {
        $assignment = $this->homework->teacherAssignment;
        $file = UploadedFile::fake()->create('instructions.pdf', 20, 'application/pdf');

        $this->actingAs($this->teacher)
            ->post(route('teacher.homework.store'), [
                'teacher_assignment_id' => $assignment->id,
                'title' => 'Homework with file',
                'description' => 'Read the attached instructions.',
                'due_date' => now()->addDays(3)->toDateString(),
                'max_score' => 10,
                'attachment' => $file,
            ])
            ->assertRedirect(route('teacher.homework'));

        $created = HomeworkAssignment::where('title', 'Homework with file')->firstOrFail();
        $this->assertNotNull($created->attachment_path);
        Storage::disk('local')->assertExists($created->attachment_path);
    }

    public function test_enrolled_student_can_submit_homework(): void
    {
        $file = UploadedFile::fake()->create('answer.pdf', 20, 'application/pdf');

        $this->actingAs($this->student)
            ->post(route('student.homework.submit', $this->homework), [
                'submission' => $file,
            ])
            ->assertRedirect(route('student.homework'));

        $submission = HomeworkSubmission::where('homework_assignment_id', $this->homework->id)->firstOrFail();
        $this->assertSame('submitted', $submission->status);
        $this->assertSame($this->student->id, $submission->student_user_id);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    public function test_teacher_can_review_a_student_submission(): void
    {
        $submission = HomeworkSubmission::create([
            'homework_assignment_id' => $this->homework->id,
            'student_user_id' => $this->student->id,
            'file_path' => 'homework/submissions/answer.pdf',
            'original_filename' => 'answer.pdf',
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        $this->actingAs($this->teacher)
            ->post(route('teacher.homework.submissions.review', $submission), [
                'status' => 'reviewed',
                'grade' => 18,
                'feedback' => 'Good work.',
            ])
            ->assertRedirect(route('teacher.homework.submissions', $this->homework));

        $this->assertDatabaseHas('homework_submissions', [
            'id' => $submission->id,
            'status' => 'reviewed',
            'grade' => 18,
            'feedback' => 'Good work.',
            'reviewed_by_user_id' => $this->teacher->id,
        ]);
    }

    public function test_student_can_download_the_homework_reference_file(): void
    {
        Storage::disk('local')->put('homework/assignments/reference.pdf', 'reference');
        $this->homework->update([
            'attachment_path' => 'homework/assignments/reference.pdf',
            'attachment_original_name' => 'reference.pdf',
        ]);

        $this->actingAs($this->student)
            ->get(route('student.homework.attachment', $this->homework))
            ->assertDownload('reference.pdf');
    }

    public function test_student_cannot_submit_homework_for_another_classroom(): void
    {
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($otherStudent)
            ->post(route('student.homework.submit', $this->homework), [
                'submission' => UploadedFile::fake()->create('answer.pdf', 20, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('homework_submissions', 0);
    }
}
