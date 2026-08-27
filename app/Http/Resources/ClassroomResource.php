<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grade_id' => $this->grade_id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ]),
            'grade' => $this->whenLoaded('grade', fn () => [
                'id' => $this->grade->id,
                'name' => $this->grade->name,
                'order_index' => $this->grade->order_index,
            ]),
            'students' => $this->whenLoaded('studentProfiles', fn () => UserResource::collection($this->studentProfiles->map->student)
            ),
        ];
    }
}
