<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreAbsenceJustificationRequest;
use App\Http\Requests\Academic\UpdateAbsenceJustificationRequest;
use App\Http\Resources\AbsenceJustificationResource;
use App\Http\Responses\ApiResponse;
use App\Models\AbsenceJustification;
use App\Models\Attendance;
use App\Services\Attendance\AbsenceJustificationService;
use Illuminate\Http\JsonResponse;

class AbsenceJustificationController extends Controller
{
    public function __construct(private AbsenceJustificationService $service) {}

    /**
     * POST /api/v1/absence-justifications
     * Role: parent only.
     */
    public function store(StoreAbsenceJustificationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($validated['attendance_id']);
        $justification = $this->service->submit(
            $request->user(),
            $attendance,
            $validated['reason'],
            $request->file('document'),
        );

        return ApiResponse::success(data: new AbsenceJustificationResource($justification->load('attendance')), status: 201);
    }

    /**
     * PUT /api/v1/absence-justifications/{id}
     * Role: teacher only.
     *
     * Body: { action: 'approve' | 'reject' }
     */
    public function update(UpdateAbsenceJustificationRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $justification = AbsenceJustification::with('attendance.classroom')->findOrFail($id);

        if ($validated['action'] === 'approve') {
            $this->service->approve($request->user(), $justification);
        } else {
            $this->service->reject($request->user(), $justification);
        }

        return ApiResponse::success(data: new AbsenceJustificationResource($justification->fresh(['attendance'])));
    }
}
