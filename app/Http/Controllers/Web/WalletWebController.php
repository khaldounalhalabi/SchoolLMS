<?php

namespace App\Http\Controllers\Web;

use App\Enums\PaymentStatus;
use App\Enums\SalaryTransferStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreSalaryTransferWebRequest;
use App\Http\Requests\Web\StoreTuitionFeeWebRequest;
use App\Models\AcademicYear;
use App\Models\Payment;
use App\Models\SalaryTransfer;
use App\Models\TuitionFee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['parent', 'student', 'academicYear', 'tuitionFee'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('parent', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $totalCollected = Payment::where('status', PaymentStatus::SUCCEEDED->value)->sum('amount');
        $totalPending = Payment::where('status', PaymentStatus::PENDING->value)->sum('amount');
        $totalFailed = Payment::where('status', PaymentStatus::FAILED->value)->sum('amount');
        $currency = config('services.stripe.currency', 'usd');

        return view('admin.wallet.index', compact(
            'payments', 'totalCollected', 'totalPending', 'totalFailed', 'currency'
        ));
    }

    public function tuitionFees(): View
    {
        $tuitionFees = TuitionFee::with('academicYear')->orderByDesc('id')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.wallet.tuition-fees', compact('tuitionFees', 'academicYears'));
    }

    public function storeTuitionFee(StoreTuitionFeeWebRequest $request): RedirectResponse
    {
        TuitionFee::updateOrCreate(
            ['academic_year_id' => $request->academic_year_id],
            [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->route('admin.wallet.tuition-fees')
            ->with('success', __('Tuition fee set successfully.'));
    }

    public function salaries(Request $request): View
    {
        $query = SalaryTransfer::with('teacher')
            ->orderByDesc('transfer_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_user_id', $request->teacher_id);
        }

        $transfers = $query->paginate(20)->withQueryString();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $totalPaid = SalaryTransfer::where('status', SalaryTransferStatus::PAID->value)->sum('amount');
        $currency = config('services.stripe.currency', 'usd');

        return view('admin.wallet.salaries', compact('transfers', 'teachers', 'totalPaid', 'currency'));
    }

    public function storeSalaryTransfer(StoreSalaryTransferWebRequest $request): RedirectResponse
    {
        SalaryTransfer::create([
            'teacher_user_id' => $request->teacher_user_id,
            'amount'          => $request->amount,
            'currency'        => $request->currency,
            'status'          => SalaryTransferStatus::PAID,
            'transfer_date'   => $request->transfer_date,
            'description'     => $request->description,
            'paid_at'         => now(),
        ]);

        return redirect()->route('admin.wallet.salaries')
            ->with('success', __('Salary transfer recorded successfully.'));
    }
}
