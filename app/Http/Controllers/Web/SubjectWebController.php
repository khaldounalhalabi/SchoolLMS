<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSubjectWebRequest;
use App\Http\Requests\Web\UpdateSubjectWebRequest;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectWebController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::with('school')->orderBy('name')->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        $schools = School::orderBy('name')->get();

        return view('admin.subjects.create', compact('schools'));
    }

    public function store(StoreSubjectWebRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')
                         ->with('success', __('Subject created successfully.'));
    }

    public function show(Subject $subject): View
    {
        $subject->load([
            'school',
            'teacherAssignments.teacher',
            'teacherAssignments.classroom.grade',
            'teacherAssignments.academicYear',
        ]);

        $teacherCount   = $subject->teacherAssignments->unique('teacher_user_id')->count();
        $classroomCount = $subject->teacherAssignments->unique('classroom_id')->count();

        return view('admin.subjects.show', compact('subject', 'teacherCount', 'classroomCount'));
    }

    public function edit(Subject $subject): View
    {
        $schools = School::orderBy('name')->get();

        return view('admin.subjects.edit', compact('subject', 'schools'));
    }

    public function update(UpdateSubjectWebRequest $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')
                         ->with('success', __('Subject updated successfully.'));
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
                         ->with('success', __('Subject deleted.'));
    }
}
