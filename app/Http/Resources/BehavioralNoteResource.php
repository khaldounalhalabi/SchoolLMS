<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BehavioralNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_user_id' => $this->student_user_id,
            'teacher_user_id' => $this->teacher_user_id,
            'note' => $this->note,
            'severity' => $this->severity,
            'date' => $this->date,
            'student' => new UserResource($this->whenLoaded('student')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
        ];
    }
}
