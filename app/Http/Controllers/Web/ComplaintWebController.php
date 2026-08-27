<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ReviewComplaintWebRequest;
use App\Http\Requests\Web\StoreComplaintWebRequest;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintWebController extends Controller
{
    public function index(): View
    {
        $complaints = Complaint::where('submitted_by_user_id', auth()->id())
            ->with('reviewer')
            ->latest()
            ->paginate(15);

        return view('complaints.index', compact('complaints'));
    }

    public function store(StoreComplaintWebRequest $request): RedirectResponse
    {
        $complaint = Complaint::create([
            ...$request->validated(),
            'submitted_by_user_id' => auth()->id(),
        ]);

        User::where('role', 'admin')->get()->each(function (User $admin) use ($complaint): void {
            $admin->notify(new SystemNotification(
                'New complaint',
                ':name submitted a new complaint.',
                route('admin.complaints.index'),
                'complaint',
                ['name' => auth()->user()->name],
            ));
        });

        return redirect()
            ->route('complaints.index')
            ->with('success', __('Complaint submitted successfully.'));
    }

    public function adminIndex(Request $request): View
    {
        $complaints = Complaint::with(['submitter', 'reviewer'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.complaints.index', compact('complaints'));
    }

    public function review(ReviewComplaintWebRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $complaint->submitter->notify(new SystemNotification(
            'Complaint updated',
            'Your complaint status has been updated to :status.',
            route('complaints.index'),
            'complaint',
            ['status' => __(ucwords(str_replace('_', ' ', $complaint->status)))],
        ));

        return redirect()
            ->route('admin.complaints.index')
            ->with('success', __('Complaint reviewed successfully.'));
    }
}
