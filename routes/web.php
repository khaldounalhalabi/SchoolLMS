<?php

use App\Http\Controllers\Web\AcademicYearWebController;
use App\Http\Controllers\Web\AdminDiagnosticWebController;
use App\Http\Controllers\Web\AdminReportWebController;
use App\Http\Controllers\Web\AdminUserController;
use App\Http\Controllers\Web\AssignmentWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CalendarWebController;
use App\Http\Controllers\Web\ClassroomWebController;
use App\Http\Controllers\Web\ComplaintWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DiagnosticKnowledgeMapWebController;
use App\Http\Controllers\Web\ExamTypeWebController;
use App\Http\Controllers\Web\GradeWebController;
use App\Http\Controllers\Web\HomeworkWebController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\NotificationWebController;
use App\Http\Controllers\Web\ParentWebController;
use App\Http\Controllers\Web\PaymentWebController;
use App\Http\Controllers\Web\SalaryWebController;
use App\Http\Controllers\Web\SchoolWebController;
use App\Http\Controllers\Web\ScheduleWebController;
use App\Http\Controllers\Web\SettingsWebController;
use App\Http\Controllers\Web\SemesterWebController;
use App\Http\Controllers\Web\StripeWebhookController;
use App\Http\Controllers\Web\StudentDiagnosticWebController;
use App\Http\Controllers\Web\StudentWebController;
use App\Http\Controllers\Web\SubjectWebController;
use App\Http\Controllers\Web\TeacherAttendanceController;
use App\Http\Controllers\Web\TeacherBehavioralNoteController;
use App\Http\Controllers\Web\TeacherGradeController;
use App\Http\Controllers\Web\TeacherWebController;
use App\Http\Controllers\Web\WalletWebController;
use Illuminate\Support\Facades\Route;

// Language switching (public — usable while logged out)
Route::get('/language/{locale}', [LocaleController::class, 'switch'])->name('language.switch');

// Auth
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->middleware('throttle:auth')->name('login.post');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

// Password reset
Route::get('/forgot-password', [AuthWebController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthWebController::class, 'sendResetLink'])->middleware('throttle:auth')->name('password.email');
Route::get('/reset-password', [AuthWebController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthWebController::class, 'resetPassword'])->middleware('throttle:auth')->name('password.update');

// Stripe Webhook (public — no auth, verified via signature)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Authenticated
Route::middleware(['auth', 'impersonation'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Settings — available to every role (language + appearance preferences).
    // Deliberately outside the role:admin group: all four roles link here from
    // the sidebar, and it is the only place the theme/language controls live
    // now that they are no longer in the topbar.
    Route::get('/settings', [SettingsWebController::class, 'index'])->name('settings.index');

    Route::get('/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');
    Route::get('/complaints', [ComplaintWebController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [ComplaintWebController::class, 'store'])->name('complaints.store');
    Route::post('/notifications/read-all', [NotificationWebController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationWebController::class, 'markRead'])->name('notifications.read');

    Route::post('/admin/stop-impersonate', [DashboardController::class, 'stopImpersonate'])
        ->middleware('actual-role:admin')
        ->name('admin.impersonate.stop');

    // Admin user management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::post('/impersonate', [DashboardController::class, 'impersonate'])->name('impersonate');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');

        // Complaints
        Route::get('/complaints', [ComplaintWebController::class, 'adminIndex'])->name('complaints.index');
        Route::post('/complaints/{complaint}/review', [ComplaintWebController::class, 'review'])->name('complaints.review');

        // Reports
        Route::get('/reports', [AdminReportWebController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportWebController::class, 'export'])->name('reports.export');
        Route::get('/reports/students/{student}/report-card', [AdminReportWebController::class, 'reportCard'])->name('reports.student-report-card');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/link-parent', [AdminUserController::class, 'linkParent'])->name('users.link-parent');
        Route::delete('/users/{user}/unlink-parent', [AdminUserController::class, 'unlinkParent'])->name('users.unlink-parent');
        Route::post('/users/{user}/link-child', [AdminUserController::class, 'linkChild'])->name('users.link-child');
        Route::delete('/users/{user}/unlink-child', [AdminUserController::class, 'unlinkChild'])->name('users.unlink-child');

        // Schools
        Route::get('/schools', [SchoolWebController::class, 'index'])->name('schools.index');
        Route::post('/schools', [SchoolWebController::class, 'store'])->name('schools.store');

        // Academic Years
        Route::get('/academic-years', [AcademicYearWebController::class, 'index'])->name('academic-years.index');
        Route::get('/academic-years/create', [AcademicYearWebController::class, 'create'])->name('academic-years.create');
        Route::get('/academic-years/{year}', [AcademicYearWebController::class, 'show'])->name('academic-years.show');
        Route::post('/academic-years/{year}/activate', [AcademicYearWebController::class, 'activate'])->name('academic-years.activate');
        Route::post('/academic-years', [AcademicYearWebController::class, 'store'])->name('academic-years.store');

        // Calendar
        Route::get('/calendar', [CalendarWebController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/create', [CalendarWebController::class, 'create'])->name('calendar.create');
        Route::get('/calendar/{event}', [CalendarWebController::class, 'show'])->name('calendar.show');
        Route::post('/calendar', [CalendarWebController::class, 'store'])->name('calendar.store');

        // Semesters
        Route::get('/semesters/create', [SemesterWebController::class, 'create'])->name('semesters.create');
        Route::post('/semesters', [SemesterWebController::class, 'store'])->name('semesters.store');

        // Schedule builder
        Route::get('/schedule', [ScheduleWebController::class, 'index'])->name('schedule.index');
        Route::post('/schedule', [ScheduleWebController::class, 'store'])->name('schedule.store');

        // Grades
        Route::get('/grades', [GradeWebController::class, 'index'])->name('grades.index');
        Route::post('/grades', [GradeWebController::class, 'store'])->name('grades.store');
        Route::delete('/grades/{grade}', [GradeWebController::class, 'destroy'])->name('grades.destroy');

        // Subjects
        Route::get('/subjects', [SubjectWebController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectWebController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectWebController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}', [SubjectWebController::class, 'show'])->name('subjects.show');
        Route::get('/subjects/{subject}/edit', [SubjectWebController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SubjectWebController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectWebController::class, 'destroy'])->name('subjects.destroy');

        // Exam Types
        Route::get('/exam-types', [ExamTypeWebController::class, 'index'])->name('exam-types.index');
        Route::post('/exam-types', [ExamTypeWebController::class, 'store'])->name('exam-types.store');
        Route::put('/exam-types/{examType}', [ExamTypeWebController::class, 'update'])->name('exam-types.update');
        Route::delete('/exam-types/{examType}', [ExamTypeWebController::class, 'destroy'])->name('exam-types.destroy');

        // Classroom management
        Route::get('/classrooms/create', [ClassroomWebController::class, 'create'])->name('classrooms.create');
        Route::post('/classrooms', [ClassroomWebController::class, 'store'])->name('classrooms.store');
        Route::post('/classrooms/{classroom}/subjects', [ClassroomWebController::class, 'storeSubject'])->name('classrooms.subjects.store');
        Route::post('/classrooms/{classroom}/students', [ClassroomWebController::class, 'enroll'])->name('classrooms.students.store');

        // Diagnostic Test Builder & Knowledge Map
        Route::get('/diagnostic/test-builder', [AdminDiagnosticWebController::class, 'testBuilder'])->name('diagnostic.test-builder');
        Route::get('/diagnostic/questions', fn () => redirect()->route('admin.diagnostic.test-builder'))->name('diagnostic.questions.index');
        Route::post('/diagnostic/objectives', [AdminDiagnosticWebController::class, 'storeObjective'])->name('diagnostic.objectives.store');
        Route::post('/diagnostic/questions', [AdminDiagnosticWebController::class, 'storeQuestion'])->name('diagnostic.questions.store');
        Route::delete('/diagnostic/questions/{question}', [AdminDiagnosticWebController::class, 'destroyQuestion'])->name('diagnostic.questions.destroy');
        Route::get('/diagnostic/knowledge-map', [DiagnosticKnowledgeMapWebController::class, 'admin'])->name('diagnostic.knowledge-map');

        // Wallet — E-Payment management
        Route::get('/wallet', [WalletWebController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/tuition-fees', [WalletWebController::class, 'tuitionFees'])->name('wallet.tuition-fees');
        Route::post('/wallet/tuition-fees', [WalletWebController::class, 'storeTuitionFee'])->name('wallet.tuition-fees.store');
        Route::get('/wallet/salaries', [WalletWebController::class, 'salaries'])->name('wallet.salaries');
        Route::post('/wallet/salaries', [WalletWebController::class, 'storeSalaryTransfer'])->name('wallet.salaries.store');

        // Teacher Assignments
        Route::get('/assignments', [AssignmentWebController::class, 'index'])->name('assignments.index');
        Route::get('/assignments/create', [AssignmentWebController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssignmentWebController::class, 'store'])->name('assignments.store');
    });

    // Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/schedule', [TeacherWebController::class, 'schedule'])->name('schedule');

        // Grade entry
        Route::get('/grades/entry', [TeacherGradeController::class, 'entry'])->name('grades.entry');
        Route::post('/grades/entry', [TeacherGradeController::class, 'store'])->name('grades.store');

        // Attendance — justifications must be declared before any future param routes
        Route::get('/attendance/justifications', [TeacherAttendanceController::class, 'justifications'])->name('justifications');
        Route::get('/attendance/justifications/{justification}/document', [TeacherAttendanceController::class, 'downloadJustificationDocument'])->name('justifications.document');
        Route::post('/attendance/justifications/{justification}/approve', [TeacherAttendanceController::class, 'approveJustification'])->name('justifications.approve');
        Route::post('/attendance/justifications/{justification}/reject', [TeacherAttendanceController::class, 'rejectJustification'])->name('justifications.reject');
        Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');

        // Behavioral notes
        Route::get('/behavioral-notes', [TeacherBehavioralNoteController::class, 'index'])->name('behavioral-notes');
        Route::post('/behavioral-notes', [TeacherBehavioralNoteController::class, 'store'])->name('behavioral-notes.store');

        // Diagnostic knowledge map (view-only for teacher)
        Route::get('/diagnostic/knowledge-map', [DiagnosticKnowledgeMapWebController::class, 'teacher'])->name('diagnostic.knowledge-map');

        // Salaries — teacher sees own salary transfers
        Route::get('/salaries', [SalaryWebController::class, 'index'])->name('salaries');

        // Homework
        Route::get('/homework', [HomeworkWebController::class, 'teacherIndex'])->name('homework');
        Route::post('/homework', [HomeworkWebController::class, 'teacherStore'])->name('homework.store');
        Route::get('/homework/{homework}/submissions', [HomeworkWebController::class, 'teacherSubmissions'])->name('homework.submissions');
        Route::post('/homework/submissions/{submission}/review', [HomeworkWebController::class, 'reviewSubmission'])->name('homework.submissions.review');
        Route::get('/homework/{homework}/attachment', [HomeworkWebController::class, 'downloadAssignment'])->name('homework.attachment');
        Route::get('/homework/submissions/{submission}/download', [HomeworkWebController::class, 'downloadSubmission'])->name('homework.submissions.download');
    });

    // Student pages
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/schedule', [StudentWebController::class, 'schedule'])->name('schedule');
        Route::get('/grades', [StudentWebController::class, 'grades'])->name('grades');
        Route::get('/homework', [HomeworkWebController::class, 'studentIndex'])->name('homework');
        Route::post('/homework/{homework}/submit', [HomeworkWebController::class, 'studentSubmit'])->name('homework.submit');
        Route::get('/homework/{homework}/attachment', [HomeworkWebController::class, 'downloadAssignment'])->name('homework.attachment');
        Route::get('/homework/submissions/{submission}/download', [HomeworkWebController::class, 'downloadSubmission'])->name('homework.submissions.download');
        Route::get('/results', [StudentWebController::class, 'results'])->name('results');
        Route::get('/results/pdf', [StudentWebController::class, 'downloadReportCard'])->name('results.pdf');
        Route::get('/attendance', [StudentWebController::class, 'attendance'])->name('attendance');

        // Diagnostic
        Route::get('/diagnostic/test', [StudentDiagnosticWebController::class, 'test'])->name('diagnostic.test');
        Route::post('/diagnostic/start', [StudentDiagnosticWebController::class, 'start'])->name('diagnostic.start');
        Route::post('/diagnostic/attempts/{attempt}/submit', [StudentDiagnosticWebController::class, 'submit'])->name('diagnostic.submit');
        Route::get('/diagnostic/knowledge-map', [StudentDiagnosticWebController::class, 'knowledgeMap'])->name('diagnostic.knowledge-map');
    });

    // Parent pages
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/children', [ParentWebController::class, 'children'])->name('children');
        Route::get('/children/{child}/schedule', [ParentWebController::class, 'childSchedule'])->name('child-schedule');
        Route::get('/children/{child}/report-card/pdf', [ParentWebController::class, 'downloadReportCard'])->name('child-report-card.pdf');
        Route::get('/grades', [ParentWebController::class, 'grades'])->name('grades');
        Route::get('/results', [ParentWebController::class, 'results'])->name('results');
        Route::get('/attendance', [ParentWebController::class, 'attendance'])->name('attendance');
        Route::get('/attendance/justifications/{justification}/document', [ParentWebController::class, 'downloadJustificationDocument'])->name('attendance.justification.document');
        Route::post('/attendance/{attendance}/justify', [ParentWebController::class, 'storeJustification'])->name('attendance.justify');
        Route::get('/behavioral-notes', [ParentWebController::class, 'behavioralNotes'])->name('behavioral-notes');

        // Payments — E-Payment via Stripe
        Route::get('/payments', [PaymentWebController::class, 'index'])->name('payments.index');
        Route::get('/payments/checkout/{tuitionFee}', [PaymentWebController::class, 'checkout'])->name('payments.checkout');
        if (app()->environment(['local', 'testing'])) {
            Route::post('/payments/test-process/{tuitionFee}', [PaymentWebController::class, 'testProcess'])->name('payments.test-process');
        }
        Route::get('/payments/success', [PaymentWebController::class, 'success'])->name('payments.success');
        Route::get('/payments/history', [PaymentWebController::class, 'history'])->name('payments.history');
    });

    // Classrooms (accessible to admin and teacher)
    Route::middleware('role:admin,teacher')->prefix('classrooms')->name('classrooms.')->group(function () {
        Route::get('/', [ClassroomWebController::class, 'index'])->name('index');
        Route::get('/{classroom}', [ClassroomWebController::class, 'show'])->name('show');
    });
});
