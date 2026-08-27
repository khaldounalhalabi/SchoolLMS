<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Diagnostic\StartAttemptRequest;
use App\Http\Requests\Web\StoreDiagnosticObjectiveRequest;
use App\Http\Requests\Web\StoreDiagnosticQuestionWebRequest;
use App\Http\Requests\Web\StudentSubmitAttemptRequest;
use App\Models\DiagnosticAttempt;
use App\Models\DiagnosticQuestion;
use Illuminate\Http\Request;

class DiagnosticWebController extends Controller
{
    public function __construct(
        private AdminDiagnosticWebController $admin,
        private DiagnosticKnowledgeMapWebController $maps,
        private StudentDiagnosticWebController $student,
    ) {}

    // Admin: Test Builder — list subjects, objectives, questions
    public function testBuilder(Request $request)
    {
        return $this->admin->testBuilder($request);
    }

    // Admin: store learning objective
    public function storeObjective(StoreDiagnosticObjectiveRequest $request)
    {
        return $this->admin->storeObjective($request);
    }

    // Admin: store question with options
    public function storeQuestion(StoreDiagnosticQuestionWebRequest $request)
    {
        return $this->admin->storeQuestion($request);
    }

    // Admin: delete question
    public function destroyQuestion(DiagnosticQuestion $question)
    {
        return $this->admin->destroyQuestion($question);
    }

    // Admin/Teacher: Knowledge Map viewer (any student)
    public function knowledgeMap(Request $request)
    {
        return $this->maps->admin($request);
    }

    // Student: take a diagnostic test
    public function studentTest(Request $request)
    {
        return $this->student->test($request);
    }

    // Student: start a new attempt
    public function studentStartAttempt(StartAttemptRequest $request)
    {
        return $this->student->start($request);
    }

    // Student: submit answers
    public function studentSubmitAttempt(
        StudentSubmitAttemptRequest $request,
        DiagnosticAttempt $attempt,
    ) {
        return $this->student->submit($request, $attempt);
    }

    // Student: view own knowledge map
    public function studentKnowledgeMap(Request $request)
    {
        return $this->student->knowledgeMap($request);
    }
}
