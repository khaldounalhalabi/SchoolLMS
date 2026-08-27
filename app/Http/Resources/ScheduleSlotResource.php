<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'classroom_id' => $this->classroom_id,
            'subject_id' => $this->subject_id,
            'teacher_user_id' => $this->teacher_user_id,
            'semester_id' => $this->semester_id,
            'day_of_week' => $this->day_of_week,
            'period_number' => $this->period_number,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
            'classroom' => new ClassroomResource($this->whenLoaded('classroom')),
            'semester' => new SemesterResource($this->whenLoaded('semester')),
        ];
    }
}
