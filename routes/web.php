<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseCategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseModuleController;
use App\Http\Controllers\ContentLessonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserRoleController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('courses', [CourseController::class, 'index'])->name('courses.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('faculties', FacultyController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('course-categories', CourseCategoryController::class)->except(['show']);
    
    Route::post('courses/assign-teacher', [CourseController::class, 'assignTeacher'])->name('courses.assign_teacher');
    Route::get('courses/{course}/modules', [CourseModuleController::class, 'index'])->name('courses.modules.index');
    Route::post('courses/{course}/modules', [CourseModuleController::class, 'store'])->name('courses.modules.store');
    Route::put('modules/{module}', [CourseModuleController::class, 'update'])->name('modules.update');
    Route::delete('modules/{module}', [CourseModuleController::class, 'destroy'])->name('modules.destroy');

    Route::get('lessons/create', [ContentLessonController::class, 'create'])->name('lessons.create');
    Route::post('lessons', [ContentLessonController::class, 'storeContent'])->name('lessons.store');
    Route::post('modules/{module}/lessons', [ContentLessonController::class, 'store'])->name('modules.lessons.store');
    Route::get('lessons/{lesson}', [ContentLessonController::class, 'show'])->name('lessons.show');
    Route::put('lessons/{lesson}', [ContentLessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [ContentLessonController::class, 'destroy'])->name('lessons.destroy');
    
    Route::resource('courses', CourseController::class)->except(['index', 'show']);
    Route::resource('teachers', TeacherController::class)->except(['show']);
    Route::resource('students', StudentController::class)->except(['show']);
    Route::resource('users', UserController::class)->except(['show']);
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('roles', [RoleManagementController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
        Route::get('roles/create', [RoleManagementController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
        Route::post('roles', [RoleManagementController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
        Route::get('roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.update');
        Route::put('roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update')->middleware('permission:roles.update');
        Route::delete('roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');
        Route::get('permissions', [PermissionManagementController::class, 'index'])->name('permissions.index')->middleware('permission:permissions.view');
        Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit')->middleware('permission:roles.view');
        Route::post('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update')->middleware('permission:roles.update');
    });
    Route::resource('academic-years', AcademicYearController::class)->except(['show']);
    Route::resource('enrollments', App\Http\Controllers\EnrollmentController::class)->except(['show']);

    // LMS Feature Routes
    Route::get('lessons', fn() => redirect()->route('lessons.create'))->name('lessons.index');
    Route::get('subjects', fn() => redirect()->route('courses.index'))->name('subjects.index');
    Route::get('exams', [App\Http\Controllers\ExamController::class, 'index'])->name('exams.index');
    Route::get('assignments', [App\Http\Controllers\AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('progress', [App\Http\Controllers\ProgressController::class, 'index'])->name('progress.index');
    Route::get('discussions', [App\Http\Controllers\DiscussionController::class, 'index'])->name('discussions.index');
    Route::get('scores', fn() => redirect()->route('dashboard'))->name('scores.index');
    Route::get('attendances', fn() => redirect()->route('dashboard'))->name('attendances.index');
    Route::get('notifications', fn() => redirect()->route('dashboard'))->name('notifications.index');
    Route::get('settings', fn() => redirect()->route('dashboard'))->name('settings.index');
});
