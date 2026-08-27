<?php

namespace App\Models;

use App\Enums\BehavioralSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BehavioralNote extends Model
{
    protected $fillable = [
        'student_user_id',
        'teacher_user_id',
        'note',
        'severity',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'severity' => BehavioralSeverity::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }
}
