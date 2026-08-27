<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreAcademicYearWebRequest;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearWebController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::withCount('semesters')->orderBy('start_date', 'desc')->get();
        return view('admin.academic-years.index', compact('years'));
    }

    public function show(AcademicYear $year): View
    {
        $year->load([
            'semesters',
            'school',
            'classrooms.grade',
            'classrooms.studentEnrollments' => fn ($query) => $query->active(),
        ]);
        return view('admin.academic-years.show', compact('year'));
    }

    public function create(): View
    {
        return view('admin.academic-years.create', [
            'hasSchool' => School::exists(),
        ]);
    }

    public function store(StoreAcademicYearWebRequest $request): RedirectResponse
    {
        $school = School::first();

        if (! $school) {
            return redirect()
                ->route('admin.schools.index')
                ->with('error', __('Create a school before adding an academic year.'));
        }

        DB::transaction(function () use ($request, $school): void {
            if ($request->boolean('is_active')) {
                AcademicYear::where('school_id', $school->id)->update(['is_active' => false]);
            }

            AcademicYear::create([
                'school_id' => $school->id,
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('admin.academic-years.index')->with('success', __('Academic year created successfully.'));
    }

    public function activate(AcademicYear $year): RedirectResponse
    {
        DB::transaction(function () use ($year): void {
            AcademicYear::where('school_id', $year->school_id)->update(['is_active' => false]);
            $year->update(['is_active' => true]);
        });

        return redirect()
            ->back()
            ->with('success', __('Academic year activated successfully.'));
    }
}
