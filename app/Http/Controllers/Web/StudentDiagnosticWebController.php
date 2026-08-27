<?php

namespace App\Http\Controllers\Web;

use App\Data\DiagnosticSubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Diagnostic\StartAttemptRequest;
use App\Http\Requests\Web\StudentSubmitAttemptRequest;
use App\Models\DiagnosticAttempt;
use App\Services\Diagnostic\DiagnosticAttemptService;
use App\Services\Diagnostic\DiagnosticKnowledgeMapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDiagnosticWebController extends Controller
{
    public function __construct(
        private DiagnosticAttemptService $attempts,
        private DiagnosticKnowledgeMapService $maps,
    ) {}

    public function test(Request $request): View
    {
        return view('student.diagnostic.test', $this->attempts->testData(
            Auth::user(),
            $request->integer('subject_id') ?: null,
        ));
    }

    public function start(StartAttemptRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->attempts->start(Auth::user(), $data['subject_id']);

        return redirect()->route('student.diagnostic.test', [
            'subject_id' => $data['subject_id'],
        ]);
    }

    public function submit(
        StudentSubmitAttemptRequest $request,
        DiagnosticAttempt $attempt,
    ): RedirectResponse {
        $attempt = $this->attempts->forStudent(Auth::user(), $attempt->id, open: true);
        $data = $request->validated();
        // Normalise from [$questionId => $optionId] to canonical shape
        $answers = collect($data['answers'])
            ->map(fn ($optionId, $questionId): array => [
                'question_id' => (int) $questionId,
                'selected_option_id' => $optionId ? (int) $optionId : null,
            ])
            ->values()
            ->all();

        $this->attempts->submitAnswers(
            $attempt,
            DiagnosticSubmissionData::fromArray(['answers' => $answers]),
        );

        return redirect()->route('student.diagnostic.knowledge-map', [
            'subject_id' => $attempt->subject_id,
        ])->with('success', 'Test submitted! Your knowledge map has been updated.');
    }

    public function knowledgeMap(Request $request): View
    {
        return view('student.diagnostic.knowledge-map', $this->maps->studentData(
            Auth::user(),
            $request->integer('subject_id') ?: null,
        ));
    }
}
