<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreSchoolCalendarRequest;
use App\Http\Resources\SchoolCalendarResource;
use App\Http\Responses\ApiResponse;
use App\Models\SchoolCalendar;
use Illuminate\Http\JsonResponse;

class SchoolCalendarController extends Controller
{
    public function index(): JsonResponse
    {
        $events = SchoolCalendar::orderBy('date')->get();

        return ApiResponse::success(data: SchoolCalendarResource::collection($events));
    }

    public function store(StoreSchoolCalendarRequest $request): JsonResponse
    {
        $event = SchoolCalendar::create($request->validated());

        return ApiResponse::success(data: new SchoolCalendarResource($event), status: 201);
    }
}
