<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiagnosticQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'learning_objective_id' => $this->learning_objective_id,
            'question_text' => $this->question_text,
            'type' => $this->type,
            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            'learning_objective' => new LearningObjectiveResource($this->whenLoaded('learningObjective')),
        ];
    }
}
