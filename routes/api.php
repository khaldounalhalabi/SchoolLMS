<?php

use App\Http\Controllers\Academic\AcademicYearController;
use App\Http\Controllers\Academic\DiagnosticController;
use App\Http\Controllers\Academic\LearningObjectiveController;
use App\Http\Controllers\Academic\ClassroomController;
use App\Http\Controllers\Academic\ParentStudentController;
use App\Http\Controllers\Academic\SchoolCalendarController;
use App\Http\Controllers\Academic\SemesterController;
use App\Http\Controllers\Academic\TeacherAssignmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->middleware('throttle:auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

// Protected auth routes
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('register', [AuthController::class, 'register'])->middleware('role:admin');
});

// User management (admin only)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}/role', [UserController::class, 'updateRole']);
    Route::patch('users/{id}/status', [UserController::class, 'updateStatus']);
});

// Roles & permissions
Route::middleware('auth:sanctum')->group(function () {
    Route::get('roles', [RoleController::class, 'index']);
});

// Academic Years (admin only)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('subjects', \App\Http\Controllers\SubjectController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('academic-years', AcademicYearController::class);
    Route::apiResource('semesters', SemesterController::class);
    Route::post('parent-student', [ParentStudentController::class, 'store']);
    Route::post('teacher-assignments', [TeacherAssignmentController::class, 'store']);
    Route::post('school-calendar', [SchoolCalendarController::class, 'store']);
});

// Classrooms (all authenticated users, filtered by role in controller)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('classrooms', [ClassroomController::class, 'index']);
    Route::get('teacher-assignments', [TeacherAssignmentController::class, 'index']);
    Route::get('school-calendar', [SchoolCalendarController::class, 'index']);
    Route::apiResource('subjects', \App\Http\Controllers\SubjectController::class)->only(['index', 'show']);

});

// Schedule slots (v1 prefix per spec)
use App\Http\Controllers\Academic\ExamTypeController;
use App\Http\Controllers\Academic\GradeController;
use App\Http\Controllers\Academic\ScheduleSlotController;
use App\Http\Controllers\Academic\AttendanceController;
use App\Http\Controllers\Academic\AbsenceJustificationController;
use App\Http\Controllers\Academic\BehavioralNoteController;

Route::prefix('v1')->group(function () {
    // Exam Types
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('exam-types', [ExamTypeController::class, 'store']);
        Route::put('exam-types/{id}', [ExamTypeController::class, 'update']);
        Route::delete('exam-types/{id}', [ExamTypeController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('exam-types', [ExamTypeController::class, 'index']);
    });

    // Grades
    Route::middleware(['auth:sanctum', 'role:teacher,admin'])->group(function () {
        Route::post('grades/bulk', [GradeController::class, 'bulkStore']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('grades', [GradeController::class, 'index']);
        Route::get('grades/class-average', [GradeController::class, 'classAverage']);
        Route::get('students/{id}/report-card', [GradeController::class, 'reportCard']);
        Route::get('students/{id}/report-card/pdf', [GradeController::class, 'reportCardPdf']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('schedule-slots', [ScheduleSlotController::class, 'store']);
        Route::put('schedule-slots/{id}', [ScheduleSlotController::class, 'update']);
        Route::delete('schedule-slots/{id}', [ScheduleSlotController::class, 'destroy']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('schedule-slots', [ScheduleSlotController::class, 'index']);
        Route::get('schedule-slots/my', [ScheduleSlotController::class, 'mySchedule']);
    });

    // Attendance
    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index']);
    });

    // Absence justifications
    Route::middleware(['auth:sanctum', 'role:parent'])->group(function () {
        Route::post('absence-justifications', [AbsenceJustificationController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        Route::put('absence-justifications/{id}', [AbsenceJustificationController::class, 'update']);
    });

    // Behavioral notes
    Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
        Route::post('behavioral-notes', [BehavioralNoteController::class, 'store']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('behavioral-notes', [BehavioralNoteController::class, 'index']);
    });

    // Diagnostic Tests & Knowledge Maps
    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::post('diagnostic-attempts', [DiagnosticController::class, 'startAttempt']);
        Route::get('diagnostic-attempts/{id}/questions', [DiagnosticController::class, 'getQuestions']);
        Route::post('diagnostic-attempts/{id}/submit', [DiagnosticController::class, 'submitAttempt']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('knowledge-map', [DiagnosticController::class, 'knowledgeMap']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('learning-objectives', [LearningObjectiveController::class, 'store']);
        Route::post('diagnostic-questions', [LearningObjectiveController::class, 'storeQuestion']);
    });
});
