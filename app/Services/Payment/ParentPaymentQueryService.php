<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatus;
use App\Models\AcademicYear;
use App\Models\Payment;
use App\Models\User;
use App\Services\Parent\ParentAccessService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ParentPaymentQueryService
{
    public function __construct(private ParentAccessService $access) {}

    public function index(User $parent): array
    {
        $children = $this->access->children($parent);
        $academicYears = AcademicYear::with(['tuitionFee' => function ($query) {
            $query->where('is_active', true);
        }])->orderByDesc('start_date')->get();
        $existingPayments = Payment::where('parent_user_id', $parent->id)
            ->whereIn('status', [PaymentStatus::PENDING->value, PaymentStatus::SUCCEEDED->value])
            ->get()
            ->keyBy(fn (Payment $payment): string => $payment->academic_year_id.'-'.$payment->student_user_id);

        return compact('parent', 'children', 'academicYears', 'existingPayments');
    }

    public function success(User $parent, string $sessionId): ?Payment
    {
        return Payment::where('stripe_checkout_session_id', $sessionId)
            ->where('parent_user_id', $parent->id)
            ->with(['academicYear', 'tuitionFee'])
            ->first();
    }

    public function history(User $parent): LengthAwarePaginator
    {
        return Payment::where('parent_user_id', $parent->id)
            ->with(['academicYear', 'student'])
            ->orderByDesc('created_at')
            ->paginate(20);
    }
}
