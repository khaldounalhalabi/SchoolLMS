<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\ScheduleSlot;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScheduleSlotTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private Classroom $classroom;

    private Subject $subject;

    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        $academicYear = AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
        ]);
        $this->semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'First Semester',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'is_active' => true,
        ]);
        $grade = Grade::create([
            'school_id' => $school->id,
            'name' => 'Grade 10',
            'order_index' => 1,
        ]);
        $this->classroom = Classroom::create([
            'grade_id' => $grade->id,
            'name' => '10-A',
            'capacity' => 30,
        ]);
        $this->subject = Subject::create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH101',
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->teacher = User::factory()->create(['role' => 'teacher']);
    }

    private function slotPayload(array $overrides = []): array
    {
        return array_merge([
            'classroom_id' => $this->classroom->id,
            'subject_id' => $this->subject->id,
            'teacher_user_id' => $this->teacher->id,
            'day_of_week' => 'monday',
            'period_number' => 1,
            'start_time' => '08:00',
            'end_time' => '09:00',
            'semester_id' => $this->semester->id,
        ], $overrides);
    }

    public function test_admin_can_create_schedule_slot_returns_201(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/schedule-slots', $this->slotPayload());

        $response->assertStatus(201)
            ->assertJsonPath('data.day_of_week', 'monday')
            ->assertJsonPath('data.period_number', 1);

        $this->assertDatabaseHas('schedule_slots', [
            'teacher_user_id' => $this->teacher->id,
            'period_number' => 1,
        ]);
    }

    public function test_duplicate_slot_returns_422(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/v1/schedule-slots', $this->slotPayload());
        $response = $this->postJson('/api/v1/schedule-slots', $this->slotPayload());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['period_number']);
    }

    public function test_teacher_sees_only_own_schedule(): void
    {
        $ownSlot = ScheduleSlot::create($this->slotPayload());

        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        ScheduleSlot::create($this->slotPayload([
            'teacher_user_id' => $otherTeacher->id,
            'period_number' => 2,
        ]));

        Sanctum::actingAs($this->teacher);
        $response = $this->getJson('/api/v1/schedule-slots/my');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($ownSlot->id, $data[0]['id']);
    }

    public function test_non_admin_cannot_create_slot(): void
    {
        Sanctum::actingAs($this->teacher);

        $response = $this->postJson('/api/v1/schedule-slots', $this->slotPayload());
        $response->assertStatus(403);
    }

    public function test_any_authenticated_user_can_read_classroom_timetable(): void
    {
        ScheduleSlot::create($this->slotPayload());

        Sanctum::actingAs($this->teacher);
        $response = $this->getJson('/api/v1/schedule-slots?classroom_id='
            .$this->classroom->id.'&semester_id='.$this->semester->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
