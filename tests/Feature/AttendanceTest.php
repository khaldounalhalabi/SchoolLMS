<?php

namespace Tests\Feature;

use App\Models\AbsenceJustification;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\BehavioralNote;
use App\Models\Classroom;
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

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private User $otherTeacher;

    private User $student;

    private User $parent;

    private Classroom $classroom;

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

        $this->classroom = Classroom::create([
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
        $this->otherTeacher = User::factory()->create(['role' => 'teacher']);
        $this->student = User::factory()->create(['role' => 'student']);
        $this->parent = User::factory()->create(['role' => 'parent']);

        // Assign teacher to classroom
        TeacherSubjectClassroom::create([
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $year->id,
        ]);

        // Enroll student in classroom
        StudentProfile::create([
            'user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'enrollment_date' => '2025-09-01',
        ]);

        // Link parent to student
        \DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'relation' => 'father',
        ]);
    }

    private function bulkPayload(array $overrides = []): array
    {
        return array_merge([
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'entries' => [
                ['student_id' => $this->student->id, 'status' => 'present'],
            ],
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // UC-13: Bulk attendance
    // ---------------------------------------------------------------

    public function test_bulk_attendance_returns_201(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/attendance/bulk', $this->bulkPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('attendance', [
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'status' => 'present',
        ]);
    }

    public function test_teacher_not_assigned_to_classroom_gets_403(): void
    {
        Sanctum::actingAs($this->otherTeacher);

        $this->postJson('/api/v1/attendance/bulk', $this->bulkPayload())
            ->assertStatus(403);
    }

    public function test_non_teacher_cannot_submit_bulk_attendance(): void
    {
        Sanctum::actingAs($this->parent);

        $this->postJson('/api/v1/attendance/bulk', $this->bulkPayload())
            ->assertStatus(403);
    }

    public function test_teacher_can_view_attendance_for_an_assigned_classroom(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('teacher.attendance', [
                'classroom_id' => $this->classroom->id,
                'date' => '2026-05-18',
            ]))
            ->assertOk();
    }

    public function test_teacher_cannot_record_attendance_for_a_student_outside_the_classroom(): void
    {
        $outsideStudent = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/attendance/bulk', $this->bulkPayload([
            'entries' => [
                ['student_id' => $outsideStudent->id, 'status' => 'present'],
            ],
        ]))->assertStatus(403);
    }

    public function test_teacher_cannot_view_attendance_for_an_unassigned_classroom(): void
    {
        $unassignedClassroom = Classroom::create([
            'grade_id' => $this->classroom->grade_id,
            'name' => '8-B',
            'capacity' => 30,
        ]);

        $this->actingAs($this->teacher)
            ->get(route('teacher.attendance', [
                'classroom_id' => $unassignedClassroom->id,
                'date' => '2026-05-18',
            ]))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // UC-14: Absence justification flow
    // ---------------------------------------------------------------

    public function test_parent_can_submit_justification(): void
    {
        $attendance = Attendance::create([
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);

        Sanctum::actingAs($this->parent);

        $this->postJson('/api/v1/absence-justifications', [
            'attendance_id' => $attendance->id,
            'reason' => 'Doctor appointment',
        ])->assertStatus(201)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_teacher_can_approve_justification_and_status_becomes_excused(): void
    {
        $attendance = Attendance::create([
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);

        $justification = AbsenceJustification::create([
            'attendance_id' => $attendance->id,
            'reason' => 'Sick',
            'submitted_by' => $this->parent->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->teacher);

        $this->putJson("/api/v1/absence-justifications/{$justification->id}", [
            'action' => 'approve',
        ])->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'status' => 'excused',
        ]);
    }

    public function test_non_parent_cannot_submit_justification(): void
    {
        $attendance = Attendance::create([
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);

        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/absence-justifications', [
            'attendance_id' => $attendance->id,
            'reason' => 'Sick',
        ])->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-15: Behavioral notes
    // ---------------------------------------------------------------

    public function test_teacher_can_create_behavioral_note(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/v1/behavioral-notes', [
            'student_user_id' => $this->student->id,
            'note' => 'Excellent participation today.',
            'severity' => 'info',
            'date' => '2026-05-18',
        ])->assertStatus(201)
            ->assertJsonPath('data.severity', 'info');

        $this->assertDatabaseHas('behavioral_notes', [
            'student_user_id' => $this->student->id,
            'teacher_user_id' => $this->teacher->id,
        ]);
    }

    public function test_behavioral_note_visible_in_get_for_correct_student(): void
    {
        BehavioralNote::create([
            'student_user_id' => $this->student->id,
            'teacher_user_id' => $this->teacher->id,
            'note' => 'Great work.',
            'severity' => 'info',
            'date' => '2026-05-18',
        ]);

        Sanctum::actingAs($this->teacher);

        $this->getJson('/api/v1/behavioral-notes?student_id='.$this->student->id)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_non_teacher_cannot_create_behavioral_note(): void
    {
        Sanctum::actingAs($this->parent);

        $this->postJson('/api/v1/behavioral-notes', [
            'student_user_id' => $this->student->id,
            'note' => 'Test',
            'severity' => 'info',
            'date' => '2026-05-18',
        ])->assertStatus(403);
    }
}
