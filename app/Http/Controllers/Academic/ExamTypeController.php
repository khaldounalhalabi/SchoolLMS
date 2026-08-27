<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreExamTypeRequest;
use App\Http\Requests\Academic\UpdateExamTypeRequest;
use App\Http\Resources\ExamTypeResource;
use App\Http\Responses\ApiResponse;
use App\Models\ExamType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ExamType::with('semester.academicYear');

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        return ApiResponse::success(data: ExamTypeResource::collection(
            $query->orderBy('semester_id')->orderBy('id')->get()
        ));
    }

    public function store(StoreExamTypeRequest $request): JsonResponse
    {
        $examType = ExamType::create($request->validated());

        return ApiResponse::success(data: new ExamTypeResource($examType->load('semester')), status: 201);
    }

    public function update(UpdateExamTypeRequest $request, int $id): JsonResponse
    {
        $examType = ExamType::findOrFail($id);

        $examType->update($request->validated());

        return ApiResponse::success(data: new ExamTypeResource($examType->load('semester')));
    }

    public function destroy(int $id): JsonResponse
    {
        ExamType::findOrFail($id)->delete();

        return ApiResponse::success(message: 'Deleted.');
    }
}
