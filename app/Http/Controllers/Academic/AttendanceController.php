<?php

namespace App\Http\Controllers\Academic;

use App\Data\BulkAttendanceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\BulkAttendanceRequest;
use App\Http\Requests\Academic\IndexAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Attendance;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    /**
     * POST /api/v1/attendance/bulk
     *
     * Body: { classroom_id, date, schedule_slot_id?, entries: [{student_id, status}] }
     */
    public function bulkStore(BulkAttendanceRequest $request): JsonResponse
    {
        $data = BulkAttendanceData::fromArray($request->validated());

        $records = $this->service->recordBulk($request->user(), $data);

        return ApiResponse::success(data: AttendanceResource::collection($records), status: 201);
    }

    /**
     * GET /api/v1/attendance?student_id=X&date_from=Y&date_to=Z
     */
    public function index(IndexAttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $request->integer('student_id');

        $student = User::findOrFail($studentId);
        $this->authorize('viewRecords', $student);

        $query = Attendance::where('student_user_id', $studentId)
            ->with(['student', 'classroom', 'scheduleSlot.subject', 'justification'])
            ->orderByDesc('date');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $paginator = $query->paginate(30);

        return ApiResponse::success(
            data: AttendanceResource::collection($paginator->items()),
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        );
    }
}
