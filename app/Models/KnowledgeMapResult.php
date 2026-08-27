<?php

namespace App\Models;

use App\Domain\MasteryLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeMapResult extends Model
{
    protected $fillable = ['student_user_id', 'learning_objective_id', 'mastery_percent', 'last_assessed_at'];

    protected $casts = [
        'last_assessed_at' => 'datetime',
        'mastery_percent'  => 'float',
    ];

    public function masteryLevel(): MasteryLevel
    {
        return MasteryLevel::fromPercent($this->mastery_percent);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class);
    }
}
