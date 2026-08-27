<?php

namespace App\Http\Controllers\Academic;

use App\Data\DiagnosticSubmissionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Diagnostic\KnowledgeMapRequest;
use App\Http\Requests\Diagnostic\StartAttemptRequest;
use App\Http\Requests\Diagnostic\SubmitAttemptRequest;
use App\Http\Resources\DiagnosticQuestionResource;
use App\Http\Responses\ApiResponse;
use App\Models\Subject;
use App\Models\User;
use App\Services\Diagnostic\DiagnosticAttemptService;
use App\Services\Diagnostic\DiagnosticKnowledgeMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    public function __construct(
        private DiagnosticAttemptService $attempts,
        private DiagnosticKnowledgeMapService $maps,
    ) {}

    // POST /diagnostic-attempts
    public function startAttempt(StartAttemptRequest $request): JsonResponse
    {
        $attempt = $this->attempts->start(
            $request->user(),
            $request->validated('subject_id'),
        );

        return ApiResponse::success(
            data: ['attempt_id' => $attempt->id, 'started_at' => $attempt->started_at],
            status: 201,
        );
    }

    // GET /diagnostic-attempts/{id}/questions
    public function getQuestions(Request $request, int $id): JsonResponse
    {
        $attempt = $this->attempts->forStudent($request->user(), $id);
        $questions = DiagnosticQuestionResource::collection(
            $this->attempts->questionsFor($attempt)
        );

        return ApiResponse::success(data: ['questions' => $questions]);
    }

    // POST /diagnostic-attempts/{id}/submit
    public function submitAttempt(SubmitAttemptRequest $request, int $id): JsonResponse
    {
        $attempt = $this->attempts->forStudent($request->user(), $id, open: true);
        $this->attempts->submitAnswers(
            $attempt,
            DiagnosticSubmissionData::fromArray($request->validated()),
        );

        return ApiResponse::success(message: 'Attempt submitted successfully.');
    }

    // GET /knowledge-map?student_id=X&subject_id=Y
    public function knowledgeMap(KnowledgeMapRequest $request): JsonResponse
    {
        $student = User::findOrFail($request->integer('student_id'));
        $subject = Subject::findOrFail($request->integer('subject_id'));
        $this->authorize('viewKnowledgeMap', [$student, $subject]);

        $tree = $this->maps->treeFor(
            $student->id,
            $request->integer('subject_id'),
        );

        return ApiResponse::success(data: [
            'tree' => $this->maps->toApiTree($tree),
        ]);
    }
}
