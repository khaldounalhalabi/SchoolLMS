<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeSummary;
use App\Models\School;
use App\Models\Semester;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teacher;

    private School $school;

    private AcademicYear $year;

    private Classroom $classroom;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->teacher = User::factory()->create(['role' => 'teacher']);

        $this->school = School::create([
            'name' => 'Test School',
            'address' => '1 Test St',
            'phone' => '+9621234567',
        ]);

        $this->year = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $grade = Grade::create([
            'school_id' => $this->school->id,
            'name' => 'Grade 7',
            'order_index' => 1,
        ]);

        $this->classroom = Classroom::create([
            'grade_id' => $grade->id,
            'academic_year_id' => $this->year->id,
            'name' => '7-A',
            'capacity' => 30,
        ]);

        $this->subject = Subject::create([
            'school_id' => $this->school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
        ]);

        TeacherSubjectClassroom::create([
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->year->id,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-33: Academic Years & Semesters
    // ---------------------------------------------------------------

    public function test_admin_can_create_academic_year(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/academic-years', [
            'school_id' => $this->school->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ])->assertStatus(201)
            ->assertJsonPath('data.name', '2026-2027');
    }

    public function test_admin_can_create_semester(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/semesters', [
            'academic_year_id' => $this->year->id,
            'name' => 'Fall Semester',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
        ])->assertStatus(201)
            ->assertJsonPath('data.name', 'Fall Semester');
    }

    public function test_admin_can_create_semester_from_the_schedule_workflow(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.semesters.store'), [
                'academic_year_id' => $this->year->id,
                'name' => 'Spring Semester',
                'start_date' => '2026-02-01',
                'end_date' => '2026-06-30',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.schedule.index', ['semester_id' => 1]));

        $this->assertDatabaseHas('semesters', [
            'academic_year_id' => $this->year->id,
            'name' => 'Spring Semester',
            'is_active' => true,
        ]);
    }

    public function test_non_admin_cannot_create_academic_year(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->postJson('/api/academic-years', [
            'school_id' => $this->school->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ])->assertStatus(403);
    }

    public function test_admin_can_create_and_manage_grades(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.grades.store'), [
                'name' => 'Grade 10',
                'order_index' => 10,
            ])
            ->assertRedirect(route('admin.grades.index'));

        $this->assertDatabaseHas('grades', [
            'school_id' => $this->school->id,
            'name' => 'Grade 10',
            'order_index' => 10,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.classrooms.create', ['academic_year_id' => $this->year->id]))
            ->assertOk()
            ->assertSee('Grade 10')
            ->assertSee(__('Manage Grades'));
    }

    public function test_admin_can_activate_an_inactive_academic_year(): void
    {
        $inactiveYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);

        $this->from(route('admin.academic-years.index'))
            ->actingAs($this->admin)
            ->post(route('admin.academic-years.activate', $inactiveYear))
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'id' => $inactiveYear->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('academic_years', [
            'id' => $this->year->id,
            'is_active' => false,
        ]);
    }

    public function test_creating_an_active_academic_year_deactivates_the_previous_one(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026-2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-06-30',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'id' => $this->year->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('academic_years', [
            'name' => '2026-2027',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_classroom_for_an_academic_year(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.store'), [
                'academic_year_id' => $this->year->id,
                'grade_id' => $this->classroom->grade_id,
                'name' => '7-B',
                'capacity' => 25,
            ])
            ->assertRedirect(route('admin.academic-years.show', $this->year));

        $this->assertDatabaseHas('classrooms', [
            'academic_year_id' => $this->year->id,
            'grade_id' => $this->classroom->grade_id,
            'name' => '7-B',
            'capacity' => 25,
        ]);
    }

    public function test_admin_can_enroll_students_in_a_classroom(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.students.store', $this->classroom), [
                'student_user_ids' => [$student->id],
            ])
            ->assertRedirect(route('classrooms.show', $this->classroom));

        $this->assertDatabaseHas('student_enrollments', [
            'student_user_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'classroom_id' => $this->classroom->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'classroom_id' => $this->classroom->id,
        ]);
    }

    public function test_admin_can_assign_a_subject_and_teacher_to_a_classroom(): void
    {
        $newSubject = Subject::create([
            'school_id' => $this->school->id,
            'name' => 'Science',
            'code' => 'SCI',
        ]);
        $newTeacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.subjects.store', $this->classroom), [
                'subject_id' => $newSubject->id,
                'teacher_user_id' => $newTeacher->id,
            ])
            ->assertRedirect(route('classrooms.show', $this->classroom));

        $this->assertDatabaseHas('teacher_subject_classroom', [
            'teacher_user_id' => $newTeacher->id,
            'subject_id' => $newSubject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->year->id,
        ]);
    }

    public function test_student_cannot_be_enrolled_twice_in_the_same_academic_year(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        StudentEnrollment::create([
            'student_user_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'classroom_id' => $this->classroom->id,
            'enrollment_date' => '2025-09-01',
            'status' => 'active',
        ]);

        $otherClassroom = Classroom::create([
            'grade_id' => $this->classroom->grade_id,
            'academic_year_id' => $this->year->id,
            'name' => '7-C',
            'capacity' => 30,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.classrooms.students.store', $otherClassroom), [
                'student_user_ids' => [$student->id],
            ])
            ->assertSessionHasErrors('student_user_ids');

        $this->assertDatabaseCount('student_enrollments', 1);
    }

    public function test_admin_can_view_academic_year_and_classroom_management_pages(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.academic-years.show', $this->year))
            ->assertOk()
            ->assertSee($this->classroom->name)
            ->assertSee('10 months');

        $this->actingAs($this->admin)
            ->get(route('classrooms.show', $this->classroom))
            ->assertOk()
            ->assertSee(__('Add Students'));
    }

    public function test_arabic_translations_are_rendered_on_teacher_and_classroom_pages(): void
    {
        app()->setLocale('ar');

        $this->actingAs($this->teacher)
            ->get(route('teacher.salaries'))
            ->assertOk()
            ->assertSee('الرواتب')
            ->assertSee('سجل الرواتب');

        $this->actingAs($this->admin)
            ->get(route('classrooms.index'))
            ->assertOk()
            ->assertSee('إدارة الفصول والطلاب المسجلين');
    }

    public function test_admin_can_filter_students_and_view_top_students(): void
    {
        $semester = Semester::create([
            'academic_year_id' => $this->year->id,
            'name' => 'Fall Semester',
            'start_date' => '2025-09-01',
            'end_date' => '2026-01-31',
            'is_active' => true,
        ]);
        $topStudent = User::factory()->create(['role' => 'student', 'name' => 'Top Student']);
        $secondStudent = User::factory()->create(['role' => 'student', 'name' => 'Second Student']);
        GradeSummary::create([
            'student_user_id' => $topStudent->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $semester->id,
            'weighted_average' => 96,
            'letter_grade' => 'A',
        ]);
        GradeSummary::create([
            'student_user_id' => $secondStudent->id,
            'subject_id' => $this->subject->id,
            'semester_id' => $semester->id,
            'weighted_average' => 88,
            'letter_grade' => 'B',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'student']))
            ->assertOk()
            ->assertSee('Top Students')
            ->assertSee('Top Student')
            ->assertSee('96.0%')
            ->assertSee('Second Student');
    }

    // ---------------------------------------------------------------
    // UC-07: Teacher assignment to classroom
    // ---------------------------------------------------------------

    public function test_teacher_sees_assigned_classrooms_across_academic_years(): void
    {
        $otherYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $otherClassroom = Classroom::create([
            'grade_id' => $this->classroom->grade_id,
            'academic_year_id' => $otherYear->id,
            'name' => '7-B-Next Year',
            'capacity' => 30,
        ]);
        TeacherSubjectClassroom::create([
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $otherClassroom->id,
            'academic_year_id' => $otherYear->id,
        ]);

        $this->actingAs($this->teacher)
            ->get(route('classrooms.index'))
            ->assertOk()
            ->assertSee($this->classroom->name)
            ->assertSee($otherClassroom->name);
    }

    public function test_admin_can_view_teacher_assignments_on_teacher_profile(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.users.show', $this->teacher))
            ->assertOk()
            ->assertSee($this->classroom->name)
            ->assertSee($this->subject->name);
    }

    public function test_assignment_rejects_a_classroom_from_another_academic_year(): void
    {
        $otherYear = AcademicYear::create([
            'school_id' => $this->school->id,
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);

        Sanctum::actingAs($this->admin);

        $this->postJson('/api/teacher-assignments', [
            'teacher_user_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $otherYear->id,
        ])->assertStatus(422);
    }

    public function test_admin_can_assign_teacher_to_classroom(): void
    {
        Sanctum::actingAs($this->admin);

        $newTeacher = User::factory()->create(['role' => 'teacher']);

        $this->postJson('/api/teacher-assignments', [
            'teacher_user_id' => $newTeacher->id,
            'subject_id' => $this->subject->id,
            'classroom_id' => $this->classroom->id,
            'academic_year_id' => $this->year->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('teacher_subject_classroom', [
            'teacher_user_id' => $newTeacher->id,
            'classroom_id' => $this->classroom->id,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-06: Parent-student linking
    // ---------------------------------------------------------------

    public function test_admin_can_delete_a_user_account(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_link_student_to_parent(): void
    {
        Sanctum::actingAs($this->admin);

        $parent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);

        $this->postJson('/api/parent-student', [
            'parent_user_id' => $parent->id,
            'student_user_id' => $student->id,
            'relation' => 'mother',
        ])->assertStatus(201);

        $this->assertDatabaseHas('parent_student', [
            'parent_user_id' => $parent->id,
            'student_user_id' => $student->id,
        ]);
    }

    public function test_student_cannot_be_linked_to_multiple_parents(): void
    {
        Sanctum::actingAs($this->admin);

        $firstParent = User::factory()->create(['role' => 'parent']);
        $secondParent = User::factory()->create(['role' => 'parent']);
        $student = User::factory()->create(['role' => 'student']);

        DB::table('parent_student')->insert([
            'parent_user_id' => $firstParent->id,
            'student_user_id' => $student->id,
            'relation' => 'father',
        ]);

        $this->postJson('/api/parent-student', [
            'parent_user_id' => $secondParent->id,
            'student_user_id' => $student->id,
            'relation' => 'mother',
        ])->assertStatus(422);

        $this->assertDatabaseCount('parent_student', 1);
    }

    public function test_parent_can_be_linked_to_multiple_students(): void
    {
        Sanctum::actingAs($this->admin);

        $parent = User::factory()->create(['role' => 'parent']);
        $firstStudent = User::factory()->create(['role' => 'student']);
        $secondStudent = User::factory()->create(['role' => 'student']);

        $this->postJson('/api/parent-student', [
            'parent_user_id' => $parent->id,
            'student_user_id' => $firstStudent->id,
            'relation' => 'father',
        ])->assertStatus(201);

        $this->postJson('/api/parent-student', [
            'parent_user_id' => $parent->id,
            'student_user_id' => $secondStudent->id,
            'relation' => 'father',
        ])->assertStatus(201);

        $this->assertDatabaseCount('parent_student', 2);
    }

    public function test_linking_non_parent_user_returns_422(): void
    {
        Sanctum::actingAs($this->admin);

        $student = User::factory()->create(['role' => 'student']);
        $notParent = User::factory()->create(['role' => 'teacher']);

        $this->postJson('/api/parent-student', [
            'parent_user_id' => $notParent->id,
            'student_user_id' => $student->id,
            'relation' => 'father',
        ])->assertStatus(422);
    }

    // ---------------------------------------------------------------
    // UC-10: School calendar
    // ---------------------------------------------------------------

    public function test_admin_can_add_calendar_event(): void
    {
        Sanctum::actingAs($this->admin);

        $this->postJson('/api/school-calendar', [
            'school_id' => $this->school->id,
            'date' => '2026-12-25',
            'type' => 'holiday',
            'description' => 'Christmas Break',
        ])->assertStatus(201);
    }

    public function test_authenticated_user_can_view_calendar(): void
    {
        Sanctum::actingAs($this->teacher);

        $this->getJson('/api/school-calendar')
            ->assertStatus(200);
    }

    // ---------------------------------------------------------------
    // Classrooms: teacher-filtered visibility
    // ---------------------------------------------------------------

    public function test_teacher_gets_only_assigned_classrooms(): void
    {
        // Create a second classroom the teacher is NOT assigned to
        $grade2 = Grade::create([
            'school_id' => $this->school->id,
            'name' => 'Grade 8',
            'order_index' => 2,
        ]);
        Classroom::create(['grade_id' => $grade2->id, 'name' => '8-A', 'capacity' => 30]);

        Sanctum::actingAs($this->teacher);

        $response = $this->getJson('/api/classrooms');
        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($this->classroom->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_admin_gets_all_classrooms(): void
    {
        Sanctum::actingAs($this->admin);

        $this->getJson('/api/classrooms')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data'); // only what setUp created
    }
}
