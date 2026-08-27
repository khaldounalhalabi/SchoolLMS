<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningObjective extends Model
{
    protected $fillable = ['subject_id', 'name', 'description', 'parent_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(LearningObjective::class, 'parent_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(DiagnosticQuestion::class);
    }

    public function knowledgeMapResults(): HasMany
    {
        return $this->hasMany(KnowledgeMapResult::class);
    }
}
