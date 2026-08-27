<?php

namespace App\Http\Controllers\Academic;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreTeacherAssignmentRequest;
use App\Http\Resources\TeacherAssignmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Classroom;
use App\Models\TeacherSubjectClassroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::TEACHER) {
            $assignments = TeacherSubjectClassroom::with(['subject', 'classroom.grade', 'academicYear'])
                ->where('teacher_user_id', $user->id)
                ->get();
        } else {
            $assignments = TeacherSubjectClassroom::with(['teacher', 'subject', 'classroom.grade', 'academicYear'])
                ->get();
        }

        return ApiResponse::success(data: TeacherAssignmentResource::collection($assignments));
    }

    public function store(StoreTeacherAssignmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $classroom = Classroom::findOrFail($data['classroom_id']);

        if ((int) $classroom->academic_year_id !== (int) $data['academic_year_id']) {
            throw ValidationException::withMessages([
                'academic_year_id' => __('The selected classroom does not belong to the selected academic year.'),
            ]);
        }

        $assignment = TeacherSubjectClassroom::firstOrCreate($data);

        return ApiResponse::success(data: new TeacherAssignmentResource($assignment->load(['subject', 'classroom.grade', 'academicYear'])), status: 201);
    }
}
