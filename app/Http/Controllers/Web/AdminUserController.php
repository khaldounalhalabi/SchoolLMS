<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\LinkChildRequest;
use App\Http\Requests\Web\LinkParentRequest;
use App\Http\Requests\Web\StoreUserRequest;
use App\Http\Requests\Web\UnlinkChildRequest;
use App\Http\Requests\Web\UnlinkParentRequest;
use App\Models\AcademicYear;
use App\Models\GradeSummary;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $allowedRoles = ['admin', 'teacher', 'student', 'parent'];
        $role = in_array(request('role'), $allowedRoles, true) ? request('role') : null;
        $users = User::when($role, fn ($query) => $query->where('role', $role))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        $topStudents = collect();

        if ($role === 'student') {
            $activeYearId = AcademicYear::where('is_active', true)->value('id');
            $topScores = GradeSummary::query()
                ->when($activeYearId, fn ($query) => $query->whereHas('semester', fn ($semesterQuery) => $semesterQuery->where('academic_year_id', $activeYearId)))
                ->select('student_user_id')
                ->selectRaw('AVG(weighted_average) as average_score')
                ->groupBy('student_user_id')
                ->orderByDesc('average_score')
                ->limit(5)
                ->get();
            $students = User::where('role', 'student')
                ->with('studentProfile.classroom.grade')
                ->whereIn('id', $topScores->pluck('student_user_id'))
                ->get()
                ->keyBy('id');

            $topStudents = $topScores->map(fn ($score) => [
                'student' => $students->get($score->student_user_id),
                'average' => round((float) $score->average_score, 1),
            ])->filter(fn (array $row) => $row['student'] !== null)->values();
        }

        return view('admin.users.index', compact('users', 'role', 'topStudents'));
    }

    public function show(User $user): View
    {
        $user->load([
            'studentProfile.classroom.grade',
            'parents',
            'children.studentProfile.classroom.grade',
            'teacherAssignments.subject',
            'teacherAssignments.classroom.grade',
            'teacherAssignments.academicYear',
        ]);

        $availableParents  = $user->role->value === 'student'
            ? User::where('role', 'parent')
                  ->whereNotIn('id', $user->parents->pluck('id'))
                  ->orderBy('name')->get()
            : collect();

        $availableStudents = $user->role->value === 'parent'
            ? User::where('role', 'student')
                  ->whereDoesntHave('parents')
                  ->orderBy('name')->get()
            : collect();

        return view('admin.users.show', compact('user', 'availableParents', 'availableStudents'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $newUser->notify(new SystemNotification(
            'Welcome to SchoolLMS',
            'Your account has been created successfully.',
            route('dashboard'),
            'account',
        ));

        return redirect()->route('admin.users.index')->with('success', __('User created successfully.'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);
        return redirect()->route('admin.users.index')->with('success', __('User status updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('User deleted successfully.'));
    }

    public function linkParent(LinkParentRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role->value === 'student', 422, __('User is not a student.'));

        $parent = User::findOrFail($request->parent_user_id);
        abort_unless($parent->role->value === 'parent', 422, __('Selected user is not a parent.'));
        abort_if($user->parents()->exists(), 422, __('This student is already linked to a parent.'));

        $exists = DB::table('parent_student')
            ->where('parent_user_id', $parent->id)
            ->where('student_user_id', $user->id)
            ->exists();

        if (! $exists) {
            DB::table('parent_student')->insert([
                'parent_user_id'  => $parent->id,
                'student_user_id' => $user->id,
                'relation'        => $request->relation,
            ]);

            $parent->notify(new SystemNotification(
                'Student linked',
                ':student is now linked to your account.',
                route('parent.children'),
                'relationship',
                ['student' => $user->name],
            ));
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('success', "{$parent->name} linked as {$request->relation}.");
    }

    public function unlinkParent(UnlinkParentRequest $request, User $user): RedirectResponse
    {
        $parent = User::findOrFail($request->parent_user_id);
        $deleted = DB::table('parent_student')
            ->where('parent_user_id', $parent->id)
            ->where('student_user_id', $user->id)
            ->delete();

        if ($deleted) {
            $parent->notify(new SystemNotification(
                'Student unlinked',
                ':student is no longer linked to your account.',
                route('parent.children'),
                'relationship',
                ['student' => $user->name],
            ));
            $user->notify(new SystemNotification(
                'Parent unlinked',
                ':parent is no longer linked to your account.',
                route('dashboard'),
                'relationship',
                ['parent' => $parent->name],
            ));
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('success', __('Parent unlinked.'));
    }

    public function linkChild(LinkChildRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->role->value === 'parent', 422, __('User is not a parent.'));

        $student = User::findOrFail($request->student_user_id);
        abort_unless($student->role->value === 'student', 422, __('Selected user is not a student.'));
        abort_if($student->parents()->exists(), 422, __('This student is already linked to a parent.'));

        $exists = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('student_user_id', $student->id)
            ->exists();

        if (! $exists) {
            DB::table('parent_student')->insert([
                'parent_user_id'  => $user->id,
                'student_user_id' => $student->id,
                'relation'        => $request->relation,
            ]);

            $user->notify(new SystemNotification(
                'Student linked',
                ':student is now linked to your account.',
                route('parent.children'),
                'relationship',
                ['student' => $student->name],
            ));
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('success', "{$student->name} linked as your child.");
    }

    public function unlinkChild(UnlinkChildRequest $request, User $user): RedirectResponse
    {
        $student = User::findOrFail($request->student_user_id);
        $deleted = DB::table('parent_student')
            ->where('parent_user_id', $user->id)
            ->where('student_user_id', $student->id)
            ->delete();

        if ($deleted) {
            $user->notify(new SystemNotification(
                'Student unlinked',
                ':student is no longer linked to your account.',
                route('parent.children'),
                'relationship',
                ['student' => $student->name],
            ));
            $student->notify(new SystemNotification(
                'Parent unlinked',
                ':parent is no longer linked to your account.',
                route('dashboard'),
                'relationship',
                ['parent' => $user->name],
            ));
        }

        return redirect()->route('admin.users.show', $user)
                         ->with('success', __('Child unlinked.'));
    }
}
