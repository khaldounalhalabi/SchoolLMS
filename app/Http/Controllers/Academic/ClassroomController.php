<?php

namespace App\Http\Controllers\Academic;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClassroomResource;
use App\Http\Responses\ApiResponse;
use App\Models\Classroom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === UserRole::TEACHER) {
            // Teachers see only classrooms they're assigned to
            $classroomIds = $user->teacherAssignments()
                ->pluck('classroom_id')
                ->unique()
                ->toArray();

            $classrooms = Classroom::with(['grade', 'academicYear', 'teacherAssignments.subject', 'studentProfiles.student'])
                ->whereIn('id', $classroomIds)
                ->get();
        } else {
            $classrooms = Classroom::with(['grade', 'academicYear', 'studentProfiles.student'])->get();
        }

        return ApiResponse::success(data: ClassroomResource::collection($classrooms));
    }
}
