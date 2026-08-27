<?php

namespace Database\Seeders;

use App\Enums\AbsenceJustificationStatus;
use App\Models\AbsenceJustification;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ExamType;
use App\Models\GradeSummary;
use App\Models\Semester;
use App\Models\StudentGrade;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Services\Grade\GradeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ReportsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025-2026')->first();

        if ($academicYear === null) {
            $this->command?->warn('Academic year 2025-2026 not found. Run DatabaseSeeder first.');

            return;
        }

        $students = collect(range(1, 10))
            ->map(fn (int $number) => User::where('email', "student{$number}@school.test")->with('studentProfile')->first())
            ->filter()
            ->values();
        $teachers = User::where('role', 'teacher')->orderBy('id')->get();
        $assignments = TeacherSubjectClassroom::where('academic_year_id', $academicYear->id)
            ->with(['subject', 'classroom'])
            ->get()
            ->groupBy('classroom_id');

        if ($students->isEmpty() || $teachers->isEmpty() || $assignments->isEmpty()) {
            $this->command?->warn('Demo students, teachers, or assignments not found. Run DatabaseSeeder first.');

            return;
        }

        $semesters = $academicYear->semesters()->orderBy('start_date')->get();
        $gradeService = app(GradeService::class);

        foreach ($semesters as $semesterIndex => $semester) {
            $examTypes = $this->examTypes($semester);

            foreach ($students as $studentIndex => $student) {
                $classroomId = $student->studentProfile?->classroom_id;
                $classAssignments = $assignments->get($classroomId, collect())->unique('subject_id')->values();

                foreach ($classAssignments as $subjectIndex => $assignment) {
                    $weightedAverage = $this->seedGrades(
                        $student,
                        $assignment,
                        $examTypes,
                        $studentIndex,
                        $subjectIndex,
                        (int) $semesterIndex,
                    );

                    GradeSummary::updateOrCreate(
                        [
                            'student_user_id' => $student->id,
                            'subject_id' => $assignment->subject_id,
                            'semester_id' => $semester->id,
                        ],
                        [
                            'weighted_average' => $weightedAverage,
                            'letter_grade' => $gradeService->letterGrade($weightedAverage),
                        ]
                    );
                }
            }

            $this->seedAttendance($semester, $students, $teachers);
        }
    }

    private function examTypes(Semester $semester): Collection
    {
        return collect([
            ['name' => 'Midterm', 'weight_percent' => 40],
            ['name' => 'Final Exam', 'weight_percent' => 60],
        ])->map(fn (array $data): ExamType => ExamType::firstOrCreate(
            ['semester_id' => $semester->id, 'name' => $data['name']],
            ['weight_percent' => $data['weight_percent']],
        ));
    }

    private function seedGrades(
        User $student,
        TeacherSubjectClassroom $assignment,
        Collection $examTypes,
        int $studentIndex,
        int $subjectIndex,
        int $semesterIndex,
    ): float {
        $base = $semesterIndex === 0
            ? 84 - (($studentIndex * 6 + $subjectIndex * 5) % 34)
            : 92 - (($studentIndex * 7 + $subjectIndex * 4) % 28);
        $scores = [max(0, $base - 6), min(100, $base + 3)];
        $weighted = 0.0;
        $totalWeight = $examTypes->sum('weight_percent');

        foreach ($examTypes->values() as $examIndex => $examType) {
            $score = (float) ($scores[$examIndex] ?? $base);

            StudentGrade::updateOrCreate(
                [
                    'student_user_id' => $student->id,
                    'subject_id' => $assignment->subject_id,
                    'exam_type_id' => $examType->id,
                ],
                [
                    'semester_id' => $examType->semester_id,
                    'teacher_user_id' => $assignment->teacher_user_id,
                    'score' => $score,
                    'max_score' => 100,
                ]
            );

            $weighted += $score * ($examType->weight_percent / $totalWeight);
        }

        return round($weighted, 2);
    }

    private function seedAttendance(Semester $semester, Collection $students, Collection $teachers): void
    {
        $dates = $semester->name === 'Fall Semester'
            ? ['2025-09-15', '2025-09-22', '2025-09-29', '2025-10-06', '2025-10-13', '2025-10-20', '2025-10-27', '2025-11-03', '2025-11-10', '2025-11-17']
            : ['2026-02-09', '2026-02-16', '2026-02-23', '2026-03-02', '2026-03-09', '2026-03-16', '2026-04-06', '2026-04-13', '2026-04-20', '2026-04-27'];

        foreach ($students as $studentIndex => $student) {
            $classroomId = $student->studentProfile?->classroom_id;

            foreach ($dates as $dateIndex => $date) {
                $status = match (($studentIndex + $dateIndex) % 12) {
                    0 => 'absent',
                    1 => 'late',
                    2 => 'excused',
                    default => 'present',
                };
                $recordedBy = $teachers->get(($studentIndex + $dateIndex) % $teachers->count());

                $attendance = Attendance::updateOrCreate(
                    [
                        'student_user_id' => $student->id,
                        'classroom_id' => $classroomId,
                        'date' => $date,
                    ],
                    [
                        'status' => $status,
                        'recorded_by' => $recordedBy->id,
                    ]
                );

                if ($studentIndex === 0 && $dateIndex === 0 && $status === 'absent') {
                    $parent = User::where('email', 'parent@school.test')->first();

                    if ($parent) {
                        AbsenceJustification::firstOrCreate(
                            ['attendance_id' => $attendance->id],
                            [
                                'reason' => 'Medical appointment',
                                'submitted_by' => $parent->id,
                                'status' => AbsenceJustificationStatus::PENDING->value,
                            ]
                        );
                    }
                }
            }
        }
    }
}
