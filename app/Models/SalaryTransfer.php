<?php

namespace App\Models;

use App\Enums\SalaryTransferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryTransfer extends Model
{
    protected $fillable = [
        'teacher_user_id',
        'amount',
        'currency',
        'status',
        'stripe_transfer_id',
        'transfer_date',
        'description',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transfer_date' => 'date',
            'paid_at' => 'datetime',
            'status' => SalaryTransferStatus::class,
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }
}
