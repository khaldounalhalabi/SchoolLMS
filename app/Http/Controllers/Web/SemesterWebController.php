<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSemesterWebRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SemesterWebController extends Controller
{
    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $selectedYearId = request()->integer('academic_year_id')
            ?: AcademicYear::where('is_active', true)->value('id');

        return view('admin.semesters.create', compact('academicYears', 'selectedYearId'));
    }

    public function store(StoreSemesterWebRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $year = AcademicYear::findOrFail($data['academic_year_id']);

        if ($data['start_date'] < $year->start_date->toDateString() || $data['end_date'] > $year->end_date->toDateString()) {
            throw ValidationException::withMessages([
                'start_date' => __('Semester dates must fall within the academic year dates.'),
            ]);
        }

        $exists = Semester::where('academic_year_id', $year->id)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('This semester already exists for the selected academic year.'),
            ]);
        }

        DB::transaction(function () use ($data, $year): void {
            if ($data['is_active'] ?? false) {
                Semester::where('academic_year_id', $year->id)->update(['is_active' => false]);
            }

            Semester::create([
                'academic_year_id' => $year->id,
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);
        });

        return redirect()
            ->route('admin.schedule.index', ['semester_id' => $year->semesters()->latest('id')->value('id')])
            ->with('success', __('Semester created successfully.'));
    }
}
