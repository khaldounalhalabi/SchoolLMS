<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSummary extends Model
{
    protected $fillable = [
        'student_user_id', 'subject_id', 'semester_id',
        'weighted_average', 'letter_grade',
    ];

    protected function casts(): array
    {
        return ['weighted_average' => 'float'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
