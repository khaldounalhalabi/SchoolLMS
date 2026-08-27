<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Grade\ReportCardService;
use App\Services\Reports\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportWebController extends Controller
{
    public function __construct(
        private AdminReportService $reports,
        private ReportCardService $reportCards,
    ) {}

    public function index(Request $request): View
    {
        return view('admin.reports.index', $this->reports->dashboard($this->validatedFilters($request)));
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->reports->exportStudents($this->validatedFilters($request));
    }

    public function reportCard(Request $request, User $student): Response
    {
        abort_unless($student->role === UserRole::STUDENT, 404);

        return $this->reportCards->download($student, $request->integer('semester_id') ?: null);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'grade_id' => ['nullable', 'integer', 'exists:grades,id'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }
}
