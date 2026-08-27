<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreAcademicYearRequest;
use App\Http\Requests\Academic\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Http\Responses\ApiResponse;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        $years = AcademicYear::with('semesters')->orderBy('start_date', 'desc')->get();

        return ApiResponse::success(data: AcademicYearResource::collection($years));
    }

    public function show(int $id): JsonResponse
    {
        $year = AcademicYear::with('semesters')->findOrFail($id);

        return ApiResponse::success(data: new AcademicYearResource($year));
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $year = AcademicYear::create($request->validated());

        return ApiResponse::success(data: new AcademicYearResource($year), status: 201);
    }

    public function update(UpdateAcademicYearRequest $request, int $id): JsonResponse
    {
        $year = AcademicYear::findOrFail($id);

        $year->update($request->validated());

        return ApiResponse::success(data: new AcademicYearResource($year));
    }

    public function destroy(int $id): JsonResponse
    {
        $year = AcademicYear::findOrFail($id);
        $year->delete();

        return ApiResponse::success(message: 'Academic year deleted.');
    }
}
