<?php

namespace App\Http\Controllers\Academic;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\IndexScheduleRequest;
use App\Http\Requests\Schedule\StoreScheduleSlotRequest;
use App\Http\Requests\Schedule\UpdateScheduleSlotRequest;
use App\Http\Resources\ScheduleSlotResource;
use App\Http\Responses\ApiResponse;
use App\Models\ScheduleSlot;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleSlotController extends Controller
{
    public function index(IndexScheduleRequest $request): JsonResponse
    {
        $slots = ScheduleSlot::with(['subject', 'teacher', 'classroom.grade'])
            ->where('classroom_id', $request->classroom_id)
            ->where('semester_id', $request->semester_id)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        return ApiResponse::success(data: ScheduleSlotResource::collection($slots));
    }

    public function mySchedule(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::STUDENT) {
            $classroom = StudentProfile::where('user_id', $user->id)->first()?->classroom;

            $slots = $classroom
                ? ScheduleSlot::with(['subject', 'teacher', 'classroom.grade', 'semester'])
                    ->where('classroom_id', $classroom->id)
                    ->orderBy('day_of_week')
                    ->orderBy('period_number')
                    ->get()
                : collect();

            return ApiResponse::success(data: ScheduleSlotResource::collection($slots));
        }

        $slots = ScheduleSlot::with(['subject', 'classroom.grade', 'semester'])
            ->where('teacher_user_id', $user->id)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();

        return ApiResponse::success(data: ScheduleSlotResource::collection($slots));
    }

    public function store(StoreScheduleSlotRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $conflict = ScheduleSlot::where('teacher_user_id', $validated['teacher_user_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where('period_number', $validated['period_number'])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'period_number' => 'This teacher already has a slot in period '
                    .$validated['period_number'].' on '
                    .$validated['day_of_week'].' this semester.',
            ]);
        }

        $slot = ScheduleSlot::create($validated);

        return ApiResponse::success(
            data: new ScheduleSlotResource($slot->load(['subject', 'teacher', 'classroom.grade', 'semester'])),
            status: 201,
        );
    }

    public function update(UpdateScheduleSlotRequest $request, int $id): JsonResponse
    {
        $slot = ScheduleSlot::findOrFail($id);
        $validated = $request->validated();

        $teacherId = $validated['teacher_user_id'] ?? $slot->teacher_user_id;
        $semesterId = $validated['semester_id'] ?? $slot->semester_id;
        $day = $validated['day_of_week'] ?? $slot->day_of_week;
        $period = $validated['period_number'] ?? $slot->period_number;

        $conflict = ScheduleSlot::where('teacher_user_id', $teacherId)
            ->where('semester_id', $semesterId)
            ->where('day_of_week', $day)
            ->where('period_number', $period)
            ->where('id', '!=', $slot->id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'period_number' => 'This teacher already has a slot in that period.',
            ]);
        }

        $slot->update($validated);

        return ApiResponse::success(data: new ScheduleSlotResource($slot->load(['subject', 'teacher', 'classroom.grade', 'semester'])));
    }

    public function destroy(int $id): JsonResponse
    {
        ScheduleSlot::findOrFail($id)->delete();

        return ApiResponse::success(message: 'Schedule slot deleted.');
    }
}
