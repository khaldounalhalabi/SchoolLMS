<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\SalaryTransfer;
use App\Models\School;
use App\Models\SchoolCalendar;
use App\Models\Semester;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\TuitionFee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users ----
        User::firstOrCreate(
            ['email' => 'admin@school.test'],
            [
                'name' => 'School Admin',
                'password' => 'password',
                'role' => 'admin',
                'phone' => '+1234567890',
                'is_active' => true,
            ]
        );

        // Demo users for each role (keep existing)
        foreach (['teacher', 'student', 'parent'] as $role) {
            User::firstOrCreate(
                ['email' => "$role@school.test"],
                [
                    'name' => ucfirst($role) . ' Demo',
                    'password' => 'password',
                    'role' => $role,
                    'is_active' => true,
                ]
            );
        }

        // Additional teacher users
        $teachers = [];
        foreach (range(1, 3) as $i) {
            $teachers[] = User::firstOrCreate(
                ['email' => "teacher{$i}@school.test"],
                [
                    'name' => "Teacher {$i}",
                    'password' => 'password',
                    'role' => 'teacher',
                    'is_active' => true,
                ]
            );
        }

        // Additional student users
        $students = [];
        foreach (range(1, 10) as $i) {
            $students[] = User::firstOrCreate(
                ['email' => "student{$i}@school.test"],
                [
                    'name' => "Student {$i}",
                    'password' => 'password',
                    'role' => 'student',
                    'is_active' => true,
                ]
            );
        }

        // Additional parent users
        $parents = [];
        foreach (range(1, 2) as $i) {
            $parents[] = User::firstOrCreate(
                ['email' => "parent{$i}@school.test"],
                [
                    'name' => "Parent {$i}",
                    'password' => 'password',
                    'role' => 'parent',
                    'is_active' => true,
                ]
            );
        }

        // ---- Permissions ----
        $permissions = [
            ['name' => 'View Users', 'slug' => 'view_users'],
            ['name' => 'Manage Users', 'slug' => 'manage_users'],
            ['name' => 'View Students', 'slug' => 'view_students'],
            ['name' => 'Manage Students', 'slug' => 'manage_students'],
            ['name' => 'View Grades', 'slug' => 'view_grades'],
            ['name' => 'Manage Grades', 'slug' => 'manage_grades'],
            ['name' => 'View Attendance', 'slug' => 'view_attendance'],
            ['name' => 'Manage Attendance', 'slug' => 'manage_attendance'],
            ['name' => 'View Reports', 'slug' => 'view_reports'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings'],
            ['name' => 'View Transport', 'slug' => 'view_transport'],
            ['name' => 'Manage Transport', 'slug' => 'manage_transport'],
            ['name' => 'View Fees', 'slug' => 'view_fees'],
            ['name' => 'Manage Fees', 'slug' => 'manage_fees'],
            ['name' => 'View Payments', 'slug' => 'view_payments'],
            ['name' => 'Manage Payments', 'slug' => 'manage_payments'],
            ['name' => 'View Salaries', 'slug' => 'view_salaries'],
            ['name' => 'Manage Salaries', 'slug' => 'manage_salaries'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], ['name' => $perm['name']]);
        }

        $rolePermissions = [
            'admin' => [
                'view_users', 'manage_users', 'view_students', 'manage_students',
                'view_grades', 'manage_grades', 'view_attendance', 'manage_attendance',
                'view_reports', 'manage_settings', 'view_transport', 'manage_transport',
                'view_fees', 'manage_fees',
                'view_payments', 'manage_payments', 'view_salaries', 'manage_salaries',
            ],
            'teacher' => [
                'view_students', 'view_grades', 'manage_grades',
                'view_attendance', 'manage_attendance', 'view_reports',
                'view_salaries',
            ],
            'student' => [
                'view_grades', 'view_attendance',
            ],
            'parent' => [
                'view_students', 'view_grades', 'view_attendance', 'view_fees', 'view_transport',
                'view_payments',
            ],
        ];

        foreach ($rolePermissions as $role => $slugs) {
            foreach ($slugs as $slug) {
                $permission = Permission::where('slug', $slug)->first();
                if ($permission) {
                    RolePermission::firstOrCreate([
                        'role' => $role,
                        'permission_id' => $permission->id,
                    ]);
                }
            }
        }

        // ---- School ----
        $school = School::firstOrCreate(
            ['name' => 'Al-Noor International School'],
            [
                'address' => '123 Education Street, Amman, Jordan',
                'phone' => '+962-6-1234567',
            ]
        );

        // ---- Academic Year ----
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2025-2026', 'school_id' => $school->id],
            [
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ]
        );

        // ---- Semesters ----
        $fallSemester = Semester::firstOrCreate(
            ['name' => 'Fall Semester', 'academic_year_id' => $academicYear->id],
            [
                'start_date' => '2025-09-01',
                'end_date' => '2026-01-31',
                'is_active' => true,
            ]
        );

        $springSemester = Semester::firstOrCreate(
            ['name' => 'Spring Semester', 'academic_year_id' => $academicYear->id],
            [
                'start_date' => '2026-02-01',
                'end_date' => '2026-06-30',
                'is_active' => false,
            ]
        );

        // ---- Grades ----
        $gradeNames = ['Grade 7', 'Grade 8', 'Grade 9'];
        $grades = [];
        foreach ($gradeNames as $index => $name) {
            $grades[] = Grade::firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                ['order_index' => $index + 1]
            );
        }

        // ---- Classrooms (2 per grade) ----
        $classrooms = [];
        foreach ($grades as $grade) {
            $gradeNum = $grade->order_index + 6; // Grade 7, 8, 9
            foreach (['A', 'B'] as $letter) {
                $classrooms[] = Classroom::firstOrCreate(
                    [
                        'name' => "{$gradeNum}-{$letter}",
                        'grade_id' => $grade->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    ['capacity' => 30]
                );
            }
        }

        // ---- Subjects ----
        $subjectData = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Arabic', 'code' => 'ARB'],
            ['name' => 'History', 'code' => 'HIST'],
        ];

        $subjects = [];
        foreach ($subjectData as $data) {
            $subjects[] = Subject::firstOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'school_id' => $school->id]
            );
        }

        // ---- Student Profiles ----
        foreach ($students as $index => $student) {
            $classroom = $classrooms[$index % count($classrooms)];
            StudentProfile::firstOrCreate(
                ['user_id' => $student->id],
                [
                    'classroom_id' => $classroom->id,
                    'enrollment_date' => '2025-09-01',
                ]
            );

            StudentEnrollment::firstOrCreate(
                [
                    'student_user_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'classroom_id' => $classroom->id,
                    'enrollment_date' => '2025-09-01',
                    'status' => 'active',
                ]
            );
        }

        // ---- Parent-Student Links ----
        // Demo parent → student 1, 2
        // Parent 1 → student 1, 2
        // Parent 2 → student 3, 4
        $demoParent = User::where('email', 'parent@school.test')->first();
        if ($demoParent && isset($students[0])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_user_id' => $demoParent->id, 'student_user_id' => $students[0]->id],
                ['relation' => 'father']
            );
        }
        if ($demoParent && isset($students[1])) {
            DB::table('parent_student')->updateOrInsert(
                ['parent_user_id' => $demoParent->id, 'student_user_id' => $students[1]->id],
                ['relation' => 'father']
            );
        }
        DB::table('parent_student')->updateOrInsert(
            ['parent_user_id' => $parents[0]->id, 'student_user_id' => $students[0]->id],
            ['relation' => 'father']
        );
        DB::table('parent_student')->updateOrInsert(
            ['parent_user_id' => $parents[0]->id, 'student_user_id' => $students[1]->id],
            ['relation' => 'father']
        );
        DB::table('parent_student')->updateOrInsert(
            ['parent_user_id' => $parents[1]->id, 'student_user_id' => $students[2]->id],
            ['relation' => 'mother']
        );
        DB::table('parent_student')->updateOrInsert(
            ['parent_user_id' => $parents[1]->id, 'student_user_id' => $students[3]->id],
            ['relation' => 'mother']
        );

        // ---- Teacher Assignments ----
        // Demo teacher → Math in 7-A, 7-B
        // Teacher 1 → Math in 7-A, 8-A
        // Teacher 2 → Science in 7-A, 7-B, 8-A
        // Teacher 3 → English in 8-B, 9-A, 9-B
        $demoTeacher = User::where('email', 'teacher@school.test')->first();
        $mathSubject = $subjects[0];
        $scienceSubject = $subjects[1];
        $englishSubject = $subjects[2];

        $assignments = [
            ['teacher' => $demoTeacher, 'subject' => $mathSubject, 'classroom' => $classrooms[0]],
            ['teacher' => $demoTeacher, 'subject' => $mathSubject, 'classroom' => $classrooms[1]],
            ['teacher' => $teachers[0], 'subject' => $mathSubject, 'classroom' => $classrooms[0]],
            ['teacher' => $teachers[0], 'subject' => $mathSubject, 'classroom' => $classrooms[2]],
            ['teacher' => $teachers[1], 'subject' => $scienceSubject, 'classroom' => $classrooms[0]],
            ['teacher' => $teachers[1], 'subject' => $scienceSubject, 'classroom' => $classrooms[1]],
            ['teacher' => $teachers[1], 'subject' => $scienceSubject, 'classroom' => $classrooms[2]],
            ['teacher' => $teachers[2], 'subject' => $englishSubject, 'classroom' => $classrooms[3]],
            ['teacher' => $teachers[2], 'subject' => $englishSubject, 'classroom' => $classrooms[4]],
            ['teacher' => $teachers[2], 'subject' => $englishSubject, 'classroom' => $classrooms[5]],
        ];

        foreach ($assignments as $assignment) {
            TeacherSubjectClassroom::firstOrCreate(
                [
                    'teacher_user_id' => $assignment['teacher']->id,
                    'subject_id' => $assignment['subject']->id,
                    'classroom_id' => $assignment['classroom']->id,
                    'academic_year_id' => $academicYear->id,
                ]
            );
        }

        // ---- School Calendar ----
        $calendarEvents = [
            ['date' => '2025-10-15', 'type' => 'event', 'description' => 'School Opening Ceremony'],
            ['date' => '2025-12-25', 'type' => 'holiday', 'description' => 'Winter Break Start'],
            ['date' => '2026-01-05', 'type' => 'holiday', 'description' => 'Winter Break End'],
            ['date' => '2026-01-15', 'type' => 'exam', 'description' => 'Midterm Exams Begin'],
            ['date' => '2026-01-25', 'type' => 'exam', 'description' => 'Midterm Exams End'],
            ['date' => '2026-03-20', 'type' => 'holiday', 'description' => 'Spring Break Start'],
            ['date' => '2026-03-30', 'type' => 'holiday', 'description' => 'Spring Break End'],
            ['date' => '2026-05-20', 'type' => 'exam', 'description' => 'Final Exams Begin'],
            ['date' => '2026-06-01', 'type' => 'exam', 'description' => 'Final Exams End'],
            ['date' => '2026-06-15', 'type' => 'event', 'description' => 'Graduation Ceremony'],
        ];

        foreach ($calendarEvents as $event) {
            SchoolCalendar::firstOrCreate(
                ['date' => $event['date'], 'school_id' => $school->id],
                [
                    'type' => $event['type'],
                    'description' => $event['description'],
                ]
            );
        }

        // ---- Tuition Fees ----
        TuitionFee::firstOrCreate(
            ['academic_year_id' => $academicYear->id],
            [
                'amount' => 5000.00,
                'currency' => 'usd',
                'is_active' => true,
            ]
        );

        // ---- Salary Transfers ----
        $allTeachers = User::where('role', 'teacher')->get();
        foreach ($allTeachers as $index => $teacher) {
            SalaryTransfer::firstOrCreate(
                [
                    'teacher_user_id' => $teacher->id,
                    'transfer_date' => '2025-10-01',
                ],
                [
                    'amount' => 2500.00 + ($index * 200),
                    'currency' => 'usd',
                    'status' => 'paid',
                    'description' => 'October salary',
                    'paid_at' => '2025-10-01 10:00:00',
                ]
            );

            SalaryTransfer::firstOrCreate(
                [
                    'teacher_user_id' => $teacher->id,
                    'transfer_date' => '2025-11-01',
                ],
                [
                    'amount' => 2500.00 + ($index * 200),
                    'currency' => 'usd',
                    'status' => 'paid',
                    'description' => 'November salary',
                    'paid_at' => '2025-11-01 10:00:00',
                ]
            );
        }

        $this->call(ReportsDemoSeeder::class);
        $this->call(DiagnosticSeeder::class);
    }
}
