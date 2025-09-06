<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProjectManagerController;
use App\Http\Controllers\DevelopmentTeamController;
use App\Http\Controllers\DevelopmentToolController;

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

    Route::get('/admin/development-teams', function () {
        return Inertia::render('development_teams/DevelopmentTeamsTable');
    })->name('admin.development-teams');
    Route::get('/admin/development-teams/create', function () {
        return Inertia::render('development_teams/CreateDevelopmentTeams');
    })->name('admin.development-teams.create');

    Route::get('/admin/development-tools', function () {
        return Inertia::render('development_tools/DevelopmentToolsTable');
    })->name('admin.development-tools');
    Route::get('/admin/development-tools/create', function () {
        return Inertia::render('development_tools/CreateDevelopmentTools');
    })->name('admin.development-tools.create');

    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/list', [UserController::class, 'list'])->name('admin.users.list');
    Route::get('/admin/users/select', [UserController::class, 'select'])->name('admin.users.select');
    Route::get('/admin/users/selectList', [UserController::class, 'selectList'])->name('admin.users.selectList');
    Route::get('/admin/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::post('/admin/users/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/delete/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/users/show/{id}', [UserController::class, 'show'])->name('admin.users.show');
    
    Route::post('/admin/project-managers', [ProjectManagerController::class, 'store'])->name('admin.project-managers.store');
    Route::get('/admin/project-managers/list', [ProjectManagerController::class, 'list'])->name('admin.project-managers.list');
    Route::get('/admin/project-managers/edit/{manager_id}', [ProjectManagerController::class, 'edit'])->name('admin.project-managers.edit');
    Route::post('/admin/project-managers/update/{manager_id}', [ProjectManagerController::class, 'update'])->name('admin.project-managers.update');
    Route::post('/admin/project-managers/delete/{manager_id}', [ProjectManagerController::class, 'destroy'])->name('admin.project-managers.destroy');
    Route::get('/admin/project-managers/selectList', [ProjectManagerController::class, 'selectList'])->name('admin.project-managers.selectList');

    Route::post('/admin/development-teams/store', [DevelopmentTeamController::class, 'store'])->name('admin.development-teams.store');
    Route::get('/admin/development-teams/list', [DevelopmentTeamController::class, 'list'])->name('admin.development-teams.list');
    Route::get('/admin/development-teams/edit/{team_id}', [DevelopmentTeamController::class, 'edit'])->name('admin.development-teams.edit');
    Route::post('/admin/development-teams/update/{team_id}', [DevelopmentTeamController::class, 'update'])->name('admin.development-teams.update');
    Route::post('/admin/development-teams/delete/{team_id}', [DevelopmentTeamController::class, 'destroy'])->name('admin.development-teams.destroy');

    Route::post('/admin/development-tools/store', [DevelopmentToolController::class, 'store'])->name('admin.development-tools.store');
    Route::get('/admin/development-tools/list', [DevelopmentToolController::class, 'list'])->name('admin.development-tools.list');
    Route::get('/admin/development-tools/edit/{tool_id}', [DevelopmentToolController::class, 'edit'])->name('admin.development-tools.edit');
    Route::post('/admin/development-tools/update/{tool_id}', [DevelopmentToolController::class, 'update'])->name('admin.development-tools.update');
    Route::post('/admin/development-tools/delete/{tool_id}', [DevelopmentToolController::class, 'destroy'])->name('admin.development-tools.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change.form');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
