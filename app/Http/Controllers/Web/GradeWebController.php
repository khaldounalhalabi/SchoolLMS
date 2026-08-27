<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSchoolGradeWebRequest;
use App\Models\Grade;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GradeWebController extends Controller
{
    public function index(): View
    {
        $grades = Grade::withCount('classrooms')
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        return view('admin.grades.index', compact('grades'));
    }

    public function store(StoreSchoolGradeWebRequest $request): RedirectResponse
    {
        $school = School::firstOrFail();
        $data = $request->validated();

        $exists = Grade::where('school_id', $school->id)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('This grade already exists.'),
            ]);
        }

        Grade::create([
            'school_id' => $school->id,
            'name' => $data['name'],
            'order_index' => $data['order_index'] ?? ((int) Grade::where('school_id', $school->id)->max('order_index') + 1),
        ]);

        return redirect()
            ->route('admin.grades.index')
            ->with('success', __('Grade created successfully.'));
    }

    public function destroy(Grade $grade): RedirectResponse
    {
        if ($grade->classrooms()->exists()) {
            throw ValidationException::withMessages([
                'grade' => __('This grade cannot be deleted because it has classrooms.'),
            ]);
        }

        $grade->delete();

        return redirect()
            ->route('admin.grades.index')
            ->with('success', __('Grade deleted successfully.'));
    }
}
