<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_user_id' => $this->student_user_id,
            'classroom_id' => $this->classroom_id,
            'schedule_slot_id' => $this->schedule_slot_id,
            'recorded_by' => $this->recorded_by,
            'date' => $this->date,
            'status' => $this->status,
            'student' => new UserResource($this->whenLoaded('student')),
            'classroom' => new ClassroomResource($this->whenLoaded('classroom')),
            'schedule_slot' => new ScheduleSlotResource($this->whenLoaded('scheduleSlot')),
            'justification' => new AbsenceJustificationResource($this->whenLoaded('justification')),
        ];
    }
}
