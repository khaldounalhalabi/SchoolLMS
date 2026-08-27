<?php

namespace App\Services\Reports;

use App\Enums\AbsenceJustificationStatus;
use App\Enums\UserRole;
use App\Models\AbsenceJustification;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Grade;
use App\Models\GradeSummary;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportService
{
    public function dashboard(array $input): array
    {
        $filters = $this->resolveFilters($input);
        $students = $this->studentsQuery($filters)->get();
        $summaries = $this->summariesQuery($filters)->get();
        $attendance = $this->attendanceQuery($filters)->get();
        $expectedSubjectsByClassroom = $this->expectedSubjectsByClassroom($filters);
        $studentRows = $this->studentRows($students, $summaries, $attendance, $filters, $expectedSubjectsByClassroom);
        $completedSummaryCount = $summaries
            ->unique(fn (GradeSummary $summary): string => "{$summary->student_user_id}-{$summary->subject_id}")
            ->count();
        $expectedSummaryCount = $this->expectedSummaryCount($students, $expectedSubjectsByClassroom);

        if ($expectedSummaryCount === 0) {
            $expectedSummaryCount = $completedSummaryCount;
        }
        $attendanceStats = $this->attendanceStats($attendance);
        $gradeDistribution = collect(['A', 'B', 'C', 'D', 'F'])
            ->mapWithKeys(fn (string $letter): array => [
                $letter => $summaries->where('letter_grade', $letter)->count(),
            ]);

        return [
            'filters' => $filters,
            'options' => $this->filterOptions($filters),
            'metrics' => [
                'students' => $students->count(),
                'average_score' => $summaries->isNotEmpty() ? round((float) $summaries->avg('weighted_average'), 1) : null,
                'pass_rate' => $summaries->isNotEmpty()
                    ? round($summaries->whereIn('letter_grade', ['A', 'B', 'C', 'D'])->count() / $summaries->count() * 100, 1)
                    : null,
                'attendance_rate' => $attendanceStats['rate'],
                'grade_completion' => $expectedSummaryCount > 0
                    ? round($completedSummaryCount / $expectedSummaryCount * 100, 1)
                    : null,
                'attention' => $studentRows->filter(fn (array $row): bool => $row['needs_attention'])->count(),
                'pending_justifications' => $this->pendingJustifications($attendance),
            ],
            'attendanceStats' => $attendanceStats,
            'gradeDistribution' => $gradeDistribution,
            'classroomPerformance' => $this->classroomPerformance($summaries),
            'attendanceByClassroom' => $this->attendanceByClassroom($attendance),
            'attentionRows' => $studentRows->where('needs_attention', true)->take(8)->values(),
            'studentRows' => $studentRows,
            'completedSummaryCount' => $completedSummaryCount,
            'expectedSummaryCount' => $expectedSummaryCount,
        ];
    }

    public function exportStudents(array $input): StreamedResponse
    {
        $rows = $this->dashboard($input)['studentRows'];
        $filename = 'student-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                __('Student'),
                __('Classroom'),
                __('Average Score'),
                __('Letter Grade'),
                __('Failed Subjects'),
                __('Missing Subjects'),
                __('Attendance Rate'),
                __('Absences'),
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['classroom'],
                    $row['average'] === null ? '' : $row['average'].'%',
                    $row['letter_grade'],
                    $row['failed_subjects'],
                    $row['missing_subjects'],
                    $row['attendance_rate'] === null ? '' : $row['attendance_rate'].'%',
                    $row['absent'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function resolveFilters(array $input): array
    {
        $hasYearFilter = array_key_exists('academic_year_id', $input);
        $academicYear = $hasYearFilter
            ? (! empty($input['academic_year_id']) ? AcademicYear::find($input['academic_year_id']) : null)
            : (AcademicYear::where('is_active', true)->orderByDesc('start_date')->first()
                ?? AcademicYear::orderByDesc('start_date')->first());

        $hasSemesterFilter = array_key_exists('semester_id', $input);
        $semester = $hasSemesterFilter
            ? ($academicYear && ! empty($input['semester_id'])
                ? Semester::where('academic_year_id', $academicYear->id)->find($input['semester_id'])
                : null)
            : $academicYear?->semesters()->orderByDesc('start_date')->first();

        return [
            'academic_year_id' => $academicYear?->id,
            'semester_id' => $semester?->id,
            'grade_id' => ! empty($input['grade_id']) ? (int) $input['grade_id'] : null,
            'classroom_id' => ! empty($input['classroom_id']) ? (int) $input['classroom_id'] : null,
            'subject_id' => ! empty($input['subject_id']) ? (int) $input['subject_id'] : null,
            'search' => trim((string) ($input['search'] ?? '')),
            'date_from' => array_key_exists('date_from', $input)
                ? ($input['date_from'] ?: null)
                : $semester?->start_date?->toDateString(),
            'date_to' => array_key_exists('date_to', $input)
                ? ($input['date_to'] ?: null)
                : $semester?->end_date?->toDateString(),
            'academic_year' => $academicYear,
            'semester' => $semester,
        ];
    }

    private function filterOptions(array $filters): array
    {
        return [
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'semesters' => Semester::when(
                $filters['academic_year_id'],
                fn (Builder $query, int $yearId) => $query->where('academic_year_id', $yearId),
            )->orderByDesc('start_date')->get(),
            'grades' => Grade::orderBy('order_index')->orderBy('name')->get(),
            'classrooms' => Classroom::with('grade')->orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ];
    }

    private function studentsQuery(array $filters): Builder
    {
        $query = User::query()->with('studentProfile.classroom.grade');
        $this->applyStudentFilters($query, $filters);

        return $query->orderBy('name');
    }

    private function summariesQuery(array $filters): Builder
    {
        $query = GradeSummary::query()
            ->with(['student.studentProfile.classroom.grade', 'subject'])
            ->when($filters['semester_id'], fn (Builder $q, int $semesterId) => $q->where('semester_id', $semesterId))
            ->when(
                ! $filters['semester_id'] && $filters['academic_year_id'],
                fn (Builder $q, int $yearId) => $q->whereHas('semester', fn (Builder $semester) => $semester->where('academic_year_id', $yearId)),
            )
            ->when($filters['subject_id'], fn (Builder $q, int $subjectId) => $q->where('subject_id', $subjectId));

        return $query->whereHas('student', fn (Builder $student) => $this->applyStudentFilters($student, $filters));
    }

    private function attendanceQuery(array $filters): Builder
    {
        $query = Attendance::query()
            ->with(['student.studentProfile.classroom.grade', 'classroom'])
            ->when($filters['date_from'], fn (Builder $q, string $date) => $q->whereDate('date', '>=', $date))
            ->when($filters['date_to'], fn (Builder $q, string $date) => $q->whereDate('date', '<=', $date));

        return $query->whereHas('student', fn (Builder $student) => $this->applyStudentFilters($student, $filters));
    }

    private function applyStudentFilters(Builder $query, array $filters): void
    {
        $query
            ->where('role', UserRole::STUDENT->value)
            ->when($filters['search'], function (Builder $student, string $search): void {
                $student->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->whereHas('studentProfile', function (Builder $profile) use ($filters): void {
                $profile->when($filters['classroom_id'], fn (Builder $q, int $classroomId) => $q->where('classroom_id', $classroomId))
                    ->whereHas('classroom', function (Builder $classroom) use ($filters): void {
                        $classroom->when($filters['grade_id'], fn (Builder $q, int $gradeId) => $q->where('grade_id', $gradeId));
                    });
            });
    }

    private function expectedSubjectIds(array $filters): Collection
    {
        return TeacherSubjectClassroom::query()
            ->when($filters['academic_year_id'], fn (Builder $q, int $yearId) => $q->where('academic_year_id', $yearId))
            ->when($filters['classroom_id'], fn (Builder $q, int $classroomId) => $q->where('classroom_id', $classroomId))
            ->when($filters['subject_id'], fn (Builder $q, int $subjectId) => $q->where('subject_id', $subjectId))
            ->when($filters['grade_id'], fn (Builder $q, int $gradeId) => $q->whereHas('classroom', fn (Builder $classroom) => $classroom->where('grade_id', $gradeId)))
            ->pluck('subject_id')
            ->unique()
            ->values();
    }

    private function expectedSubjectsByClassroom(array $filters): Collection
    {
        return TeacherSubjectClassroom::query()
            ->when($filters['academic_year_id'], fn (Builder $q, int $yearId) => $q->where('academic_year_id', $yearId))
            ->when($filters['classroom_id'], fn (Builder $q, int $classroomId) => $q->where('classroom_id', $classroomId))
            ->when($filters['subject_id'], fn (Builder $q, int $subjectId) => $q->where('subject_id', $subjectId))
            ->when($filters['grade_id'], fn (Builder $q, int $gradeId) => $q->whereHas('classroom', fn (Builder $classroom) => $classroom->where('grade_id', $gradeId)))
            ->get(['classroom_id', 'subject_id'])
            ->groupBy('classroom_id')
            ->map(fn (Collection $rows): Collection => $rows->pluck('subject_id')->unique()->values());
    }

    private function expectedSummaryCount(Collection $students, Collection $expectedSubjectsByClassroom): int
    {
        return $students->sum(
            fn (User $student): int => $expectedSubjectsByClassroom
                ->get($student->studentProfile?->classroom_id, collect())
                ->count()
        );
    }

    private function studentRows(
        Collection $students,
        Collection $summaries,
        Collection $attendance,
        array $filters,
        Collection $expectedSubjectsByClassroom,
    ): Collection {
        $summariesByStudent = $summaries->groupBy('student_user_id');
        $attendanceByStudent = $attendance->groupBy('student_user_id');
        $subjectIds = $this->expectedSubjectIds($filters);

        if ($subjectIds->isEmpty()) {
            $subjectIds = $summaries->pluck('subject_id')->unique()->values();
        }

        return $students->map(function (User $student) use ($summariesByStudent, $attendanceByStudent, $subjectIds, $expectedSubjectsByClassroom): array {
            $studentSummaries = $summariesByStudent->get($student->id, collect());
            $studentAttendance = $attendanceByStudent->get($student->id, collect());
            $studentSubjectIds = $expectedSubjectsByClassroom->get(
                $student->studentProfile?->classroom_id,
                $subjectIds,
            );
            $attendanceStats = $this->attendanceStats($studentAttendance);
            $average = $studentSummaries->isNotEmpty() ? round((float) $studentSummaries->avg('weighted_average'), 1) : null;
            $failedSubjects = $studentSummaries->where('letter_grade', 'F')->count();
            $missingSubjects = max(0, $studentSubjectIds->count() - $studentSummaries->unique('subject_id')->count());

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'classroom' => $student->studentProfile?->classroom?->name ?? '—',
                'average' => $average,
                'letter_grade' => $this->letterGrade($average),
                'failed_subjects' => $failedSubjects,
                'missing_subjects' => $missingSubjects,
                'attendance_rate' => $attendanceStats['rate'],
                'absent' => $attendanceStats['absent'],
                'needs_attention' => $average === null
                    || $average < 60
                    || $failedSubjects > 0
                    || $missingSubjects > 0
                    || ($attendanceStats['rate'] !== null && $attendanceStats['rate'] < 90),
            ];
        })->sortBy(function (array $row): array {
            return [$row['needs_attention'] ? 0 : 1, $row['average'] ?? -1, $row['name']];
        })->values();
    }

    private function attendanceStats(Collection $records): array
    {
        $counts = $records->countBy(fn (Attendance $record): string => $record->status->value);
        $present = (int) $counts->get('present', 0);
        $absent = (int) $counts->get('absent', 0);
        $late = (int) $counts->get('late', 0);
        $excused = (int) $counts->get('excused', 0);
        $eligible = $present + $absent + $late;

        return [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'excused' => $excused,
            'total' => $records->count(),
            'rate' => $eligible > 0 ? round(($present + $late) / $eligible * 100, 1) : null,
        ];
    }

    private function classroomPerformance(Collection $summaries): Collection
    {
        return $summaries
            ->groupBy(fn (GradeSummary $summary): string => $summary->student?->studentProfile?->classroom?->name ?? '—')
            ->map(function (Collection $rows, string $classroom): array {
                return [
                    'classroom' => $classroom,
                    'students' => $rows->pluck('student_user_id')->unique()->count(),
                    'average' => round((float) $rows->avg('weighted_average'), 1),
                    'pass_rate' => round($rows->whereIn('letter_grade', ['A', 'B', 'C', 'D'])->count() / max(1, $rows->count()) * 100, 1),
                ];
            })
            ->sortByDesc('average')
            ->values();
    }

    private function attendanceByClassroom(Collection $attendance): Collection
    {
        return $attendance
            ->groupBy(fn (Attendance $record): string => $record->classroom?->name ?? '—')
            ->map(function (Collection $rows, string $classroom): array {
                $stats = $this->attendanceStats($rows);

                return [
                    'classroom' => $classroom,
                    'students' => $rows->pluck('student_user_id')->unique()->count(),
                    'rate' => $stats['rate'],
                    'records' => $stats['total'],
                ];
            })
            ->sortByDesc(fn (array $row): float => $row['rate'] ?? -1)
            ->values();
    }

    private function pendingJustifications(Collection $attendance): int
    {
        if ($attendance->isEmpty()) {
            return 0;
        }

        return AbsenceJustification::whereIn('attendance_id', $attendance->pluck('id'))
            ->where('status', AbsenceJustificationStatus::PENDING->value)
            ->count();
    }

    private function letterGrade(?float $average): string
    {
        return match (true) {
            $average === null => '—',
            $average >= 90 => 'A',
            $average >= 80 => 'B',
            $average >= 70 => 'C',
            $average >= 60 => 'D',
            default => 'F',
        };
    }
}
