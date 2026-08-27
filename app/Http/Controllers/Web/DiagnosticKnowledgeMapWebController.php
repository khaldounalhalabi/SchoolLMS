<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Diagnostic\DiagnosticKnowledgeMapService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiagnosticKnowledgeMapWebController extends Controller
{
    public function __construct(private DiagnosticKnowledgeMapService $maps) {}

    public function admin(Request $request): View
    {
        return view('admin.diagnostic.knowledge-map', $this->maps->adminData(
            $request->integer('subject_id') ?: null,
            $request->integer('student_id') ?: null,
        ));
    }

    public function teacher(Request $request): View
    {
        return view('admin.diagnostic.knowledge-map', $this->maps->teacherData(
            $request->user(),
            $request->integer('subject_id') ?: null,
            $request->integer('student_id') ?: null,
        ));
    }
}
