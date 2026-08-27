<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScheduleSlot;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherWebController extends Controller
{
    public function schedule(): View
    {
        $user = Auth::user();

        $allSlots = ScheduleSlot::where('teacher_user_id', $user->id)
            ->with(['subject', 'classroom.grade', 'semester'])
            ->orderBy('period_number')
            ->get()
            ->groupBy('day_of_week');

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $selectedDay = request('day', strtolower(now()->format('l')));

        // Default to Sunday if today is Friday/Saturday
        if (!in_array($selectedDay, $days)) {
            $selectedDay = 'sunday';
        }

        $slots = $allSlots->get($selectedDay, collect());

        return view('teacher.schedule', compact('allSlots', 'days', 'selectedDay', 'slots'));
    }
}
