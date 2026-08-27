<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreSemesterRequest;
use App\Http\Requests\Academic\UpdateSemesterRequest;
use App\Http\Resources\SemesterResource;
use App\Http\Responses\ApiResponse;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    public function index(): JsonResponse
    {
        $semesters = Semester::with('academicYear')->orderBy('start_date', 'desc')->get();

        return ApiResponse::success(data: SemesterResource::collection($semesters));
    }

    public function show(int $id): JsonResponse
    {
        $semester = Semester::with('academicYear')->findOrFail($id);

        return ApiResponse::success(data: new SemesterResource($semester));
    }

    public function store(StoreSemesterRequest $request): JsonResponse
    {
        $semester = Semester::create($request->validated());

        return ApiResponse::success(data: new SemesterResource($semester), status: 201);
    }

    public function update(UpdateSemesterRequest $request, int $id): JsonResponse
    {
        $semester = Semester::findOrFail($id);

        $semester->update($request->validated());

        return ApiResponse::success(data: new SemesterResource($semester));
    }

    public function destroy(int $id): JsonResponse
    {
        $semester = Semester::findOrFail($id);
        $semester->delete();

        return ApiResponse::success(message: 'Semester deleted.');
    }
}
