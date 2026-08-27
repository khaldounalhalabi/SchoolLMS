<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Http\Responses\ApiResponse;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(data: SubjectResource::collection(
            Subject::with('school')->orderBy('name')->get()
        ));
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return ApiResponse::success(data: new SubjectResource($subject->load('school')), status: 201);
    }

    public function show(int $id): JsonResponse
    {
        return ApiResponse::success(data: new SubjectResource(
            Subject::with('school')->findOrFail($id)
        ));
    }

    public function update(UpdateSubjectRequest $request, int $id): JsonResponse
    {
        $subject = Subject::findOrFail($id);

        $subject->update($request->validated());

        return ApiResponse::success(data: new SubjectResource($subject->load('school')));
    }

    public function destroy(int $id): JsonResponse
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return ApiResponse::success(message: 'Subject deleted.');
    }
}
