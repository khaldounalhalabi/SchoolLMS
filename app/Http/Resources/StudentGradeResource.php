<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_user_id' => $this->student_user_id,
            'subject_id' => $this->subject_id,
            'exam_type_id' => $this->exam_type_id,
            'semester_id' => $this->semester_id,
            'teacher_user_id' => $this->teacher_user_id,
            'score' => $this->score,
            'max_score' => $this->max_score,
            'student' => new UserResource($this->whenLoaded('student')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'exam_type' => new ExamTypeResource($this->whenLoaded('examType')),
            'semester' => new SemesterResource($this->whenLoaded('semester')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
        ];
    }
}
