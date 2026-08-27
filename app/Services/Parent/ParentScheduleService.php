<?php

namespace App\Services\Parent;

use App\Models\ScheduleSlot;
use App\Models\User;

class ParentScheduleService
{
    private const DAYS = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

    public function __construct(private ParentAccessService $access) {}

    public function forChild(User $parent, User $child, ?string $requestedDay): array
    {
        $child = $this->access->assertChild($parent, $child);
        $classroom = $child->studentProfile?->classroom;

        $allSlots = $classroom
            ? ScheduleSlot::where('classroom_id', $classroom->id)
                ->with(['subject', 'teacher', 'classroom.grade'])
                ->orderBy('period_number')
                ->get()
                ->groupBy('day_of_week')
            : collect();

        $selectedDay = $requestedDay ?: strtolower(now()->format('l'));

        if (! in_array($selectedDay, self::DAYS, true)) {
            $selectedDay = 'sunday';
        }

        return [
            'allSlots' => $allSlots,
            'days' => self::DAYS,
            'selectedDay' => $selectedDay,
            'slots' => $allSlots->get($selectedDay, collect()),
            'classroom' => $classroom,
            'child' => $child,
        ];
    }
}
