<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordChangeController;

Route::middleware(['auth', 'verified', 'must_change_password'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Homepage');
    })->name('home');
});

Route::middleware(['auth', 'admin', 'must_change_password'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('admin/AdminDashboard');
    })->name('admin.dashboard');

    Route::get('/admin/users', function () {
        return Inertia::render('users/UsersTable');
    })->name('admin.users');
    Route::get('/admin/users/create', function () {
        return Inertia::render('users/CreateUsers');
    })->name('admin.users.create');

    Route::get('/admin/project-managers', function () {
        return Inertia::render('project_managers/ProjectManagersTable');
    })->name('admin.project-managers');

    Route::get('/admin/project-managers/create', function () {
        return Inertia::render('project_managers/CreateProjectManagers');
    })->name('admin.project-manager.create');

    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/list', [UserController::class, 'list'])->name('admin.users.list');
    Route::get('/admin/users/selectList', [UserController::class, 'selectList'])->name('admin.users.selectList');
    Route::get('/admin/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::post('/admin/users/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/delete/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/users/show/{id}', [UserController::class, 'show'])->name('admin.users.show');
    
    Route::post('/admin/project-managers', [App\Http\Controllers\ProjectManagerController::class, 'store'])->name('admin.project-managers.store');
    Route::get('/admin/project-managers/list', [App\Http\Controllers\ProjectManagerController::class, 'list'])->name('admin.project-managers.list');
    Route::get('/admin/project-managers/edit/{id}', [App\Http\Controllers\ProjectManagerController::class, 'edit'])->name('admin.project-managers.edit');
    Route::post('/admin/project-managers/update/{id}', [App\Http\Controllers\ProjectManagerController::class, 'update'])->name('admin.project-managers.update');
    Route::post('/admin/project-managers/delete/{id}', [App\Http\Controllers\ProjectManagerController::class, 'destroy'])->name('admin.project-managers.destroy');

});

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change.form');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
