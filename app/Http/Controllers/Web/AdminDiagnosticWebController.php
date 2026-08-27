<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreDiagnosticObjectiveRequest;
use App\Http\Requests\Web\StoreDiagnosticQuestionWebRequest;
use App\Models\DiagnosticQuestion;
use App\Services\Diagnostic\DiagnosticCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDiagnosticWebController extends Controller
{
    public function __construct(private DiagnosticCatalogService $catalog) {}

    public function testBuilder(Request $request): View
    {
        return view('admin.diagnostic.test-builder', $this->catalog->builderData(
            $request->integer('subject_id') ?: null,
        ));
    }

    public function storeObjective(StoreDiagnosticObjectiveRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->catalog->createObjective($data);

        return redirect()->route('admin.diagnostic.test-builder', [
            'subject_id' => $data['subject_id'],
        ])->with('success', __('Learning objective added.'));
    }

    public function storeQuestion(StoreDiagnosticQuestionWebRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->catalog->createQuestion($data, $request->integer('correct_option'));

        return redirect()->route('admin.diagnostic.test-builder', [
            'subject_id' => $data['subject_id'],
        ])->with('success', __('Question added.'));
    }

    public function destroyQuestion(DiagnosticQuestion $question): RedirectResponse
    {
        $subjectId = $question->subject_id;
        $this->catalog->deleteQuestion($question);

        return redirect()->route('admin.diagnostic.test-builder', [
            'subject_id' => $subjectId,
        ])->with('success', __('Question deleted.'));
    }
}
