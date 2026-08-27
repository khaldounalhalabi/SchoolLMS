<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleSlotRequest;
use App\Models\Classroom;
use App\Models\ScheduleSlot;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleWebController extends Controller
{
    public function index(Request $request): View
    {
        $classrooms  = Classroom::with('grade')->orderBy('name')->get();
        $semesters   = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();
        $teachers    = User::where('role', 'teacher')->select('id', 'name')->orderBy('name')->get();
        $subjects    = Subject::select('id', 'name', 'code')->orderBy('name')->get();

        $classroomId = $request->integer('classroom_id') ?: null;
        $semesterId  = $request->integer('semester_id')  ?: null;

        $slots = [];
        if ($classroomId && $semesterId) {
            $slots = ScheduleSlot::with(['subject', 'teacher'])
                ->where('classroom_id', $classroomId)
                ->where('semester_id', $semesterId)
                ->get()
                ->keyBy(fn ($s) => $s->day_of_week->value . '_' . $s->period_number);
        }

        return view('admin.schedule.index', compact(
            'classrooms', 'semesters', 'teachers', 'subjects',
            'slots', 'classroomId', 'semesterId'
        ));
    }

    public function store(StoreScheduleSlotRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $conflict = ScheduleSlot::where('teacher_user_id', $validated['teacher_user_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('period_number', $validated['period_number'])
            ->exists();

        if ($conflict) {
            return back()
                ->withInput()
                ->withErrors(['period_number' => 'This teacher already has a slot at that period on that day this semester.']);
        }

        ScheduleSlot::create($validated);

        return redirect()->route('admin.schedule.index', [
            'classroom_id' => $validated['classroom_id'],
            'semester_id'  => $validated['semester_id'],
        ]);
    }
}
