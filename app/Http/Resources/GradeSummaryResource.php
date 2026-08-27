<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_user_id' => $this->student_user_id,
            'subject_id' => $this->subject_id,
            'semester_id' => $this->semester_id,
            'weighted_average' => $this->weighted_average,
            'letter_grade' => $this->letter_grade,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'semester' => new SemesterResource($this->whenLoaded('semester')),
        ];
    }
}
