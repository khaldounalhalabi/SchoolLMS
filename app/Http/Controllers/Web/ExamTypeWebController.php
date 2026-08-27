<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreExamTypeWebRequest;
use App\Http\Requests\Web\UpdateExamTypeWebRequest;
use App\Models\ExamType;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamTypeWebController extends Controller
{
    public function index(): View
    {
        $examTypes = ExamType::with('semester.academicYear')->orderByDesc('id')->paginate(20);
        $semesters = Semester::with('academicYear')->orderByDesc('id')->get();

        return view('admin.exam-types.index', compact('examTypes', 'semesters'));
    }

    public function store(StoreExamTypeWebRequest $request): RedirectResponse
    {
        ExamType::create($request->validated());

        return redirect()->route('admin.exam-types.index')->with('success', __('Exam type created.'));
    }

    public function update(UpdateExamTypeWebRequest $request, ExamType $examType): RedirectResponse
    {
        $examType->update($request->validated());

        return redirect()->route('admin.exam-types.index')->with('success', __('Exam type updated.'));
    }

    public function destroy(ExamType $examType): RedirectResponse
    {
        $examType->delete();

        return redirect()->route('admin.exam-types.index')->with('success', __('Exam type deleted.'));
    }
}
