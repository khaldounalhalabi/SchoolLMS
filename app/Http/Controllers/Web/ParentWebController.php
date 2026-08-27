<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreJustificationWebRequest;
use App\Models\AbsenceJustification;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AbsenceJustificationService;
use App\Services\Parent\ParentAcademicService;
use App\Services\Parent\ParentAccessService;
use App\Services\Parent\ParentAttendanceService;
use App\Services\Parent\ParentBehavioralNoteService;
use App\Services\Parent\ParentScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParentWebController extends Controller
{
    public function __construct(
        private ParentAccessService $access,
        private ParentAcademicService $academic,
        private ParentAttendanceService $attendance,
        private AbsenceJustificationService $justifications,
        private ParentBehavioralNoteService $behavioralNotes,
        private ParentScheduleService $schedule,
    ) {}

    public function children(): View
    {
        $user = Auth::user();
        $children = $this->access->children($user);

        return view('parent.children', compact('user', 'children'));
    }

    public function childSchedule(Request $request, User $child): View
    {
        return view('parent.child-schedule', $this->schedule->forChild(
            Auth::user(),
            $child,
            $request->input('day'),
        ));
    }

    public function results(Request $request): View
    {
        return view('parent.results', $this->academic->results(
            Auth::user(),
            $request->input('child_id'),
            $request->integer('semester_id') ?: null,
        ));
    }

    public function grades(): View
    {
        $user = Auth::user();
        $children = $this->access->children($user);

        return view('parent.grades', compact('user', 'children'));
    }

    public function downloadReportCard(Request $request, User $child): Response
    {
        return $this->academic->download(
            Auth::user(),
            $child,
            $request->integer('semester_id') ?: null,
        );
    }

    public function attendance(Request $request): View
    {
        return view('parent.attendance', $this->attendance->records(
            Auth::user(),
            $request->input('child_id'),
            $request->input('date_from'),
            $request->input('date_to'),
        ));
    }

    public function behavioralNotes(Request $request): View
    {
        return view('parent.behavioral-notes', $this->behavioralNotes->forParent(
            Auth::user(),
            $request->input('child_id'),
        ));
    }

    public function downloadJustificationDocument(AbsenceJustification $justification): StreamedResponse
    {
        return $this->justifications->download(Auth::user(), $justification);
    }

    public function storeJustification(
        StoreJustificationWebRequest $request,
        Attendance $attendance,
    ): RedirectResponse {
        $this->justifications->submit(
            Auth::user(),
            $attendance,
            $request->input('reason'),
            $request->file('document'),
        );

        return redirect()
            ->route('parent.attendance', ['child_id' => $attendance->student_user_id])
            ->with('success', __('Justification submitted successfully.'));
    }
}
