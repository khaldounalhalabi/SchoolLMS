<?php

namespace App\Http\Controllers\Web;

use App\Enums\SalaryTransferStatus;
use App\Http\Controllers\Controller;
use App\Models\SalaryTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalaryWebController extends Controller
{
    public function index(): View
    {
        $teacher = Auth::user();

        $transfers = SalaryTransfer::where('teacher_user_id', $teacher->id)
            ->orderByDesc('transfer_date')
            ->paginate(20);

        $totalPaid = SalaryTransfer::where('teacher_user_id', $teacher->id)
            ->where('status', SalaryTransferStatus::PAID->value)
            ->sum('amount');

        $currency = config('services.stripe.currency', 'usd');

        return view('teacher.salaries.index', compact('teacher', 'transfers', 'totalPaid', 'currency'));
    }
}
