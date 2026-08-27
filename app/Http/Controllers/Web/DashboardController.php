<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ScheduleSlot;
use App\Models\StudentProfile;
use App\Models\TeacherSubjectClassroom;
use App\Models\User;
use App\Services\ImpersonationService;
use App\Services\Parent\ParentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private ImpersonationService $impersonation,
        private ParentAccessService $parentAccess,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $role = UserRole::tryFrom(session('impersonate_role', $user->role->value)) ?? $user->role;

        return match ($role) {
            UserRole::ADMIN => view('dashboards.admin', ['user' => $user, 'stats' => $this->adminStats()]),
            UserRole::TEACHER => $this->teacherDashboard($user),
            UserRole::STUDENT => $this->studentDashboard($user),
            UserRole::PARENT => $this->parentDashboard($user),
            default => view('dashboards.admin', ['user' => $user, 'stats' => $this->adminStats()]),
        };
    }

    private function teacherDashboard(User $user): View
    {
        $today = Weekday::tryFrom(strtolower(now()->format('l')));

        $classrooms = Classroom::whereHas('teacherAssignments', fn ($q) => $q->where('teacher_user_id', $user->id))
            ->with('grade')
            ->withCount('studentProfiles')
            ->get();

        $assignments = TeacherSubjectClassroom::where('teacher_user_id', $user->id)
            ->with(['subject', 'classroom.grade'])
            ->get();

        $todaySlots = $today
            ? ScheduleSlot::where('teacher_user_id', $user->id)
                ->where('day_of_week', $today->value)
                ->with(['subject', 'classroom'])
                ->orderBy('period_number')
                ->get()
            : collect();

        return view('dashboards.teacher', compact('user', 'classrooms', 'assignments', 'todaySlots'));
    }

    private function studentDashboard(User $user): View
    {
        $profile = StudentProfile::where('user_id', $user->id)->first();
        $classroom = $profile?->classroom;
        $today = Weekday::tryFrom(strtolower(now()->format('l')));

        $todaySlots = $classroom && $today
            ? ScheduleSlot::where('classroom_id', $classroom->id)
                ->where('day_of_week', $today->value)
                ->with(['subject', 'teacher'])
                ->orderBy('period_number')
                ->get()
            : collect();

        return view('dashboards.student', compact('user', 'classroom', 'todaySlots'));
    }

    private function parentDashboard(User $user): View
    {
        $children = $this->parentAccess->children($user);

        return view('dashboards.parent', compact('user', 'children'));
    }

    private function adminStats(): array
    {
        return [
            'total_users' => User::count(),
            'admins' => User::where('role', UserRole::ADMIN->value)->count(),
            'teachers' => User::where('role', UserRole::TEACHER->value)->count(),
            'students' => User::where('role', UserRole::STUDENT->value)->count(),
            'parents' => User::where('role', UserRole::PARENT->value)->count(),
            'active_users' => User::where('is_active', true)->count(),
        ];
    }

    public function impersonate(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === UserRole::ADMIN, 403);

        $validated = $request->validate([
            'role' => [
                'required',
                Rule::in([
                    UserRole::TEACHER->value,
                    UserRole::STUDENT->value,
                    UserRole::PARENT->value,
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->impersonation->start(
            $request->user(),
            UserRole::from($validated['role']),
            $validated['reason'] ?? null,
        );

        return redirect()->route('dashboard');
    }

    public function stopImpersonate(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === UserRole::ADMIN, 403);

        $this->impersonation->stop($request->user());

        return redirect()->route('dashboard');
    }
}
