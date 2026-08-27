<?php

namespace App\Models;

use App\Enums\DiagnosticQuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiagnosticQuestion extends Model
{
    protected $fillable = ['subject_id', 'learning_objective_id', 'question_text', 'type'];

    protected function casts(): array
    {
        return [
            'type' => DiagnosticQuestionType::class,
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id');
    }
}
