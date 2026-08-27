<?php

namespace App\Models;

use App\Enums\AbsenceJustificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceJustification extends Model
{
    protected $fillable = [
        'attendance_id',
        'reason',
        'submitted_by',
        'document_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => AbsenceJustificationStatus::class,
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
