<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\IndexBehavioralNoteRequest;
use App\Http\Requests\Academic\StoreBehavioralNoteRequest;
use App\Http\Resources\BehavioralNoteResource;
use App\Http\Responses\ApiResponse;
use App\Models\BehavioralNote;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class BehavioralNoteController extends Controller
{
    /**
     * POST /api/v1/behavioral-notes
     * Role: teacher only.
     */
    public function store(StoreBehavioralNoteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $student = User::findOrFail($validated['student_user_id']);
        $this->authorize('viewRecords', $student);

        $note = BehavioralNote::create([
            ...$validated,
            'teacher_user_id' => $request->user()->id,
        ]);

        return ApiResponse::success(data: new BehavioralNoteResource($note->load(['student', 'teacher'])), status: 201);
    }

    /**
     * GET /api/v1/behavioral-notes?student_id=X
     */
    public function index(IndexBehavioralNoteRequest $request): JsonResponse
    {
        $user = $request->user();
        $studentId = $request->integer('student_id');

        $student = User::findOrFail($studentId);
        $this->authorize('viewRecords', $student);

        $notes = BehavioralNote::where('student_user_id', $studentId)
            ->with(['teacher', 'student'])
            ->orderByDesc('date')
            ->paginate(20);

        return ApiResponse::success(
            data: BehavioralNoteResource::collection($notes->items()),
            meta: [
                'current_page' => $notes->currentPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'last_page' => $notes->lastPage(),
            ],
        );
    }
}
