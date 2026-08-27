<?php

namespace Tests\Feature;

use App\Models\DiagnosticAttempt;
use App\Models\DiagnosticQuestion;
use App\Models\LearningObjective;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiagnosticTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $otherStudent;

    private Subject $subject;

    private DiagnosticQuestion $question;

    private QuestionOption $correctOption;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        $this->subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Math',
            'code' => 'MATH',
        ]);

        $objective = LearningObjective::create([
            'subject_id' => $this->subject->id,
            'name' => 'Algebra',
            'description' => 'Algebra fundamentals',
        ]);

        $this->question = DiagnosticQuestion::create([
            'subject_id' => $this->subject->id,
            'learning_objective_id' => $objective->id,
            'question_text' => 'What is 2 + 2?',
            'type' => 'mcq',
        ]);

        $this->correctOption = QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => '5',
            'is_correct' => false,
        ]);

        $this->student = User::factory()->create(['role' => 'student']);
        $this->otherStudent = User::factory()->create(['role' => 'student']);
    }

    public function test_student_can_start_and_complete_a_diagnostic_attempt(): void
    {
        Sanctum::actingAs($this->student);

        $startResponse = $this->postJson('/api/v1/diagnostic-attempts', [
            'subject_id' => $this->subject->id,
        ])->assertCreated();

        $attemptId = $startResponse->json('data.attempt_id');

        $this->postJson("/api/v1/diagnostic-attempts/{$attemptId}/submit", [
            'answers' => [[
                'question_id' => $this->question->id,
                'selected_option_id' => $this->correctOption->id,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('diagnostic_attempts', [
            'id' => $attemptId,
        ]);
        $this->assertDatabaseHas('diagnostic_answers', [
            'attempt_id' => $attemptId,
            'question_id' => $this->question->id,
            'selected_option_id' => $this->correctOption->id,
            'is_correct' => true,
        ]);
        $this->assertNotNull(DiagnosticAttempt::find($attemptId)->completed_at);
    }

    public function test_student_cannot_submit_a_question_from_another_subject(): void
    {
        $otherSubject = Subject::create([
            'school_id' => $this->subject->school_id,
            'name' => 'Science',
            'code' => 'SCI',
        ]);
        $otherObjective = LearningObjective::create([
            'subject_id' => $otherSubject->id,
            'name' => 'Physics',
        ]);
        $otherQuestion = DiagnosticQuestion::create([
            'subject_id' => $otherSubject->id,
            'learning_objective_id' => $otherObjective->id,
            'question_text' => 'What is gravity?',
            'type' => 'mcq',
        ]);
        $otherOption = QuestionOption::create([
            'question_id' => $otherQuestion->id,
            'option_text' => 'A force',
            'is_correct' => true,
        ]);

        $attempt = DiagnosticAttempt::create([
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/v1/diagnostic-attempts/{$attempt->id}/submit", [
            'answers' => [[
                'question_id' => $otherQuestion->id,
                'selected_option_id' => $otherOption->id,
            ]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('diagnostic_answers', 0);
        $this->assertNull($attempt->fresh()->completed_at);
    }

    public function test_student_cannot_submit_an_option_from_another_question(): void
    {
        $otherOption = QuestionOption::create([
            'question_id' => DiagnosticQuestion::create([
                'subject_id' => $this->subject->id,
                'learning_objective_id' => $this->question->learning_objective_id,
                'question_text' => 'What is 3 + 3?',
                'type' => 'mcq',
            ])->id,
            'option_text' => '6',
            'is_correct' => true,
        ]);
        $attempt = DiagnosticAttempt::create([
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->student);

        $this->postJson("/api/v1/diagnostic-attempts/{$attempt->id}/submit", [
            'answers' => [[
                'question_id' => $this->question->id,
                'selected_option_id' => $otherOption->id,
            ]],
        ])->assertStatus(422);

        $this->assertDatabaseCount('diagnostic_answers', 0);
    }

    public function test_student_cannot_submit_another_students_attempt(): void
    {
        $attempt = DiagnosticAttempt::create([
            'student_user_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'started_at' => now(),
        ]);

        Sanctum::actingAs($this->otherStudent);

        $this->postJson("/api/v1/diagnostic-attempts/{$attempt->id}/submit", [
            'answers' => [[
                'question_id' => $this->question->id,
                'selected_option_id' => $this->correctOption->id,
            ]],
        ])->assertNotFound();
    }

    public function test_admin_can_create_true_false_question_without_empty_options(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.diagnostic.questions.store'), [
                'subject_id' => $this->subject->id,
                'learning_objective_id' => $this->question->learning_objective_id,
                'question_text' => 'The sky is blue.',
                'type' => 'true_false',
                'correct_option' => 0,
                'options' => [
                    ['option_text' => 'True'],
                    ['option_text' => 'False'],
                    ['option_text' => ''],
                    ['option_text' => ''],
                ],
            ])
            ->assertRedirect();

        $createdQuestion = DiagnosticQuestion::query()
            ->where('question_text', 'The sky is blue.')
            ->firstOrFail();

        $this->assertSame(2, $createdQuestion->options()->count());
        $this->assertDatabaseHas('question_options', [
            'question_id' => $createdQuestion->id,
            'option_text' => 'True',
            'is_correct' => true,
        ]);
    }

    public function test_split_student_diagnostic_route_renders(): void
    {
        $this->actingAs($this->student)
            ->get(route('student.diagnostic.test'))
            ->assertOk();
    }
}
