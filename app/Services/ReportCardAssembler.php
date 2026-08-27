<?php

namespace App\Services;

use App\Domain\ReportCardData;
use App\Models\ExamType;
use App\Models\GradeSummary;
use App\Models\Semester;
use App\Models\StudentGrade;
use App\Models\User;

class ReportCardAssembler
{
    public function assemble(int $studentId, ?int $semesterId): ReportCardData
    {
        $student = User::with('studentProfile.classroom.grade')->findOrFail($studentId);
        $semester = $semesterId
            ? Semester::with('academicYear')->findOrFail($semesterId)
            : null;

        return $this->build($student, $semesterId, $semester);
    }

    public function assembleStudent(User $student, ?int $semesterId): ReportCardData
    {
        $semester = $semesterId
            ? Semester::with('academicYear')->find($semesterId)
            : null;

        return $this->build($student, $semesterId, $semester);
    }

    private function build(User $student, ?int $semesterId, ?Semester $semester): ReportCardData
    {
        $summaries = GradeSummary::where('student_user_id', $student->id)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->with(['subject', 'semester.academicYear'])
            ->get();

        $grades = StudentGrade::where('student_user_id', $student->id)
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->with(['subject', 'examType'])
            ->get();

        $examTypes = $semesterId
            ? ExamType::where('semester_id', $semesterId)->orderBy('id')->get()
            : collect();

        return new ReportCardData($student, $semester, $summaries, $grades, $examTypes);
    }
}
