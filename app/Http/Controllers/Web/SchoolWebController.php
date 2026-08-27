<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSchoolWebRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolWebController extends Controller
{
    public function index(): View
    {
        $schools = School::withCount(['academicYears', 'grades', 'subjects'])
            ->orderBy('name')
            ->get();

        return view('admin.schools.index', compact('schools'));
    }

    public function store(StoreSchoolWebRequest $request): RedirectResponse
    {
        School::create($request->validated());

        return redirect()
            ->route('admin.schools.index')
            ->with('success', __('School created successfully.'));
    }
}
