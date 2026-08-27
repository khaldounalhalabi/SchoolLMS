<?php

namespace Tests\Feature;

use App\Models\AbsenceJustification;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParentWebControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $parent;

    private User $student;

    private User $otherStudent;

    private User $teacher;

    private Classroom $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        $school = School::create(['name' => 'Test School']);
        AcademicYear::create([
            'school_id' => $school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
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

        $this->parent = User::factory()->create(['role' => 'parent']);
        $this->student = User::factory()->create(['role' => 'student', 'name' => 'Linked Child']);
        $this->otherStudent = User::factory()->create(['role' => 'student', 'name' => 'Unlinked Child']);
        $this->teacher = User::factory()->create(['role' => 'teacher']);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'enrollment_date' => '2025-09-01',
        ]);

        StudentProfile::create([
            'user_id' => $this->otherStudent->id,
            'classroom_id' => $this->classroom->id,
            'enrollment_date' => '2025-09-01',
        ]);

        DB::table('parent_student')->insert([
            'parent_user_id' => $this->parent->id,
            'student_user_id' => $this->student->id,
            'relation' => 'father',
        ]);
    }

    public function test_parent_cannot_view_an_unlinked_child_schedule(): void
    {
        $this->actingAs($this->parent)
            ->get(route('parent.child-schedule', $this->otherStudent))
            ->assertForbidden();
    }

    public function test_invalid_child_selection_falls_back_to_an_authorized_child(): void
    {
        $this->actingAs($this->parent)
            ->get(route('parent.results', ['child_id' => $this->otherStudent->id]))
            ->assertOk()
            ->assertSee('Linked Child')
            ->assertDontSee('Unlinked Child');
    }

    public function test_parent_can_submit_a_web_absence_justification(): void
    {
        $attendance = Attendance::create([
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);

        $this->actingAs($this->parent)
            ->post(route('parent.attendance.justify', $attendance), [
                'reason' => 'Doctor appointment',
            ])
            ->assertRedirect(route('parent.attendance', ['child_id' => $this->student->id]));

        $this->assertDatabaseHas('absence_justifications', [
            'attendance_id' => $attendance->id,
            'submitted_by' => $this->parent->id,
            'reason' => 'Doctor appointment',
            'status' => 'pending',
        ]);
    }

    public function test_parent_cannot_submit_justification_for_an_unlinked_child(): void
    {
        $attendance = Attendance::create([
            'student_user_id' => $this->otherStudent->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);

        $this->actingAs($this->parent)
            ->post(route('parent.attendance.justify', $attendance), [
                'reason' => 'Unauthorized submission',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('absence_justifications', 0);
    }

    public function test_justification_document_is_downloaded_only_by_a_linked_parent(): void
    {
        Storage::fake('local');

        $attendance = Attendance::create([
            'student_user_id' => $this->student->id,
            'classroom_id' => $this->classroom->id,
            'date' => '2026-05-18',
            'status' => 'absent',
            'recorded_by' => $this->teacher->id,
        ]);
        $path = 'justifications/medical-note.pdf';
        Storage::disk('local')->put($path, 'private document');
        $justification = AbsenceJustification::create([
            'attendance_id' => $attendance->id,
            'reason' => 'Doctor appointment',
            'submitted_by' => $this->parent->id,
            'document_path' => $path,
            'status' => 'pending',
        ]);

        $this->actingAs($this->parent)
            ->get(route('parent.attendance.justification.document', $justification))
            ->assertDownload("justification-{$justification->id}.pdf");

        $this->actingAs(User::factory()->create(['role' => 'parent']))
            ->get(route('parent.attendance.justification.document', $justification))
            ->assertForbidden();
    }
}
