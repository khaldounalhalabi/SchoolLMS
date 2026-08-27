<?php

namespace App\Services\Grade;

use App\Domain\ReportCardData;
use App\Models\Semester;
use App\Models\User;
use App\Services\ReportCardAssembler;
use App\Services\ReportCardPdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mpdf\Output\Destination;

class ReportCardService
{
    public function __construct(
        private ReportCardAssembler $assembler,
        private ReportCardPdfService $pdf,
    ) {}

    public function semesters(): Collection
    {
        return Semester::with('academicYear')->orderByDesc('id')->get();
    }

    public function studentResults(User $student, ?int $semesterId): array
    {
        $semesters = $this->semesters();
        $selectedSemesterId = $semesterId ?: $semesters->first()?->id;

        if (! $selectedSemesterId) {
            return [
                'user' => $student,
                'semesters' => $semesters,
                'selectedSemesterId' => null,
                'summaries' => collect(),
                'grades' => collect(),
                'examTypes' => collect(),
            ];
        }

        $data = $this->assembler->assembleStudent($student, $selectedSemesterId);

        return [
            'user' => $student,
            'semesters' => $semesters,
            'selectedSemesterId' => $selectedSemesterId,
            'summaries' => $data->summaries,
            'grades' => $data->grades->groupBy('subject_id'),
            'examTypes' => $data->examTypes,
        ];
    }

    public function assemble(User $student, ?int $semesterId): ReportCardData
    {
        return $this->assembler->assembleStudent($student, $semesterId);
    }

    public function assembleById(int $studentId, ?int $semesterId): ReportCardData
    {
        return $this->assembler->assemble($studentId, $semesterId);
    }

    public function download(User $student, ?int $semesterId): Response
    {
        $data = $this->assemble($student, $semesterId);
        $student = $data->student;
        $semester = $data->semester;
        $summaries = $data->summaries;
        $grades = $data->grades->groupBy('subject_id');
        $examTypes = $data->examTypes;
        $filename = 'report_card_'.Str::slug($student->name).'_'.($semester?->id ?? $semesterId ?? 'latest').'.pdf';

        $mpdf = $this->pdf->render(compact('student', 'semester', 'summaries', 'grades', 'examTypes'));

        return response($mpdf->Output($filename, Destination::STRING_RETURN))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
