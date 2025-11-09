<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TestingTeamController;
use App\Http\Controllers\TestingToolController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ProjectManagerController;
use App\Http\Controllers\DevelopmentTeamController;
use App\Http\Controllers\DevelopmentToolController;
use App\Http\Controllers\ProjectProgressController;

Route::middleware(['auth', 'verified', 'must_change_password'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Homepage');
    })->name('home');

    Route::get('/projects', function () {
        return Inertia::render('projects/ProjectsList');
    })->name('projects.list');

    Route::get('/projects/projectListManager', [ProjectController::class, 'projectListManager'])->name('projects.projectListManager');
    Route::get('/projects/viewProjectDetails/{project_id}', [ProjectController::class, 'viewProjectDetails'])->name('projects.viewProjectDetails');
    Route::get('/projects/getProjectProgress/{project_id}', [ProjectController::class, 'getProjectProgress'])->name('projects.getProjectProgress');
    Route::post('/projects/addProgress', [ProjectController::class, 'addProgress'])->name('projects.addProgress');
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

    Route::get('/admin/testing-teams', function () {
        return Inertia::render('testing_teams/TestingTeamsTable');
    })->name('admin.testing-teams');
    Route::get('/admin/testing-teams/create', function () {
        return Inertia::render('testing_teams/CreateTestingTeams');
    })->name('admin.testing-teams.create');

    Route::get('/admin/testing-tools', function () {
        return Inertia::render('testing_tools/TestingToolsTable');
    })->name('admin.testing-tools');
    Route::get('/admin/testing-tools/create', function () {
        return Inertia::render('testing_tools/CreateTestingTools');
    })->name('admin.testing-tools.create');

    Route::get('/admin/projects', function () {
        return Inertia::render('projects/ProjectsTable');
    })->name('admin.projects');
    Route::get('/admin/projects/create', function () {
        return Inertia::render('projects/CreateProjects');
    })->name('admin.projects.create');

    Route::get('/admin/clients', function () {
        return Inertia::render('clients/ClientsTable');
    })->name('admin.clients');
    Route::get('/admin/clients/create', function () {
        return Inertia::render('clients/CreateClient');
    })->name('admin.clients.create');

    
    Route::get('/admin/project_progress', [ProjectProgressController::class, 'index'])->name('admin.project_progress');
    Route::get('/admin/project_progress/create', [ProjectProgressController::class, 'create'])->name('admin.project_progress.create');

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
    Route::get('/admin/development-teams/selectList', [DevelopmentTeamController::class, 'selectList'])->name('admin.development-teams.selectList');

    Route::post('/admin/development-tools/store', [DevelopmentToolController::class, 'store'])->name('admin.development-tools.store');
    Route::get('/admin/development-tools/list', [DevelopmentToolController::class, 'list'])->name('admin.development-tools.list');
    Route::get('/admin/development-tools/edit/{tool_id}', [DevelopmentToolController::class, 'edit'])->name('admin.development-tools.edit');
    Route::post('/admin/development-tools/update/{tool_id}', [DevelopmentToolController::class, 'update'])->name('admin.development-tools.update');
    Route::post('/admin/development-tools/delete/{tool_id}', [DevelopmentToolController::class, 'destroy'])->name('admin.development-tools.destroy');
    
    Route::post('/admin/testing-teams/store', [TestingTeamController::class, 'store'])->name('admin.testing-teams.store');
    Route::get('/admin/testing-teams/list', [TestingTeamController::class, 'list'])->name('admin.testing-teams.list');
    Route::get('/admin/testing-teams/edit/{testing_team_id}', [TestingTeamController::class, 'edit'])->name('admin.testing-teams.edit');
    Route::post('/admin/testing-teams/update/{testing_team_id}', [TestingTeamController::class, 'update'])->name('admin.testing-teams.update');
    Route::post('/admin/testing-teams/delete/{testing_team_id}', [TestingTeamController::class, 'destroy'])->name('admin.testing-teams.destroy');
    Route::get('/admin/testing-teams/selectList', [TestingTeamController::class, 'selectList'])->name('admin.testing-teams.selectList');
    
    Route::post('/admin/testing-tools/store', [TestingToolController::class, 'store'])->name('admin.testing-tools.store');
    Route::get('/admin/testing-tools/list', [TestingToolController::class, 'list'])->name('admin.testing-tools.list');
    Route::get('/admin/testing-tools/edit/{testing_tool_id}', [TestingToolController::class, 'edit'])->name('admin.testing-tools.edit');
    Route::post('/admin/testing-tools/update/{testing_tool_id}', [TestingToolController::class, 'update'])->name('admin.testing-tools.update');
    Route::post('/admin/testing-tools/delete/{testing_tool_id}', [TestingToolController::class, 'destroy'])->name('admin.testing-tools.destroy');
    Route::get('/admin/testing-tools/selectList', [TestingToolController::class, 'selectList'])->name('admin.testing-tools.selectList');

    Route::post('/admin/projects/store', [ProjectController::class, 'store'])->name('admin.projects.store');
    Route::get('/admin/projects/list', [ProjectController::class, 'list'])->name('admin.projects.list');
    Route::get('/admin/projects/edit/{project_id}', [ProjectController::class, 'edit'])->name('admin.projects.edit');
    Route::post('/admin/projects/update/{project_id}', [ProjectController::class, 'update'])->name('admin.projects.update');
    Route::post('/admin/projects/delete/{project_id}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
    Route::get('/admin/projects/selectList', [ProjectController::class, 'selectList'])->name('admin.projects.selectList');

    Route::post('/admin/clients/store', [ClientController::class, 'store'])->name('admin.clients.store');
    Route::get('/admin/clients/list', [ClientController::class, 'list'])->name('admin.clients.list');
    Route::get('/admin/clients/edit/{client_id}', [ClientController::class, 'edit'])->name('admin.clients.edit');
    Route::post('/admin/clients/update/{client_id}', [ClientController::class, 'update'])->name('admin.clients.update');
    Route::post('/admin/clients/delete/{client_id}', [ClientController::class, 'destroy'])->name('admin.clients.destroy');
    Route::get('/admin/clients/selectList', [ClientController::class, 'selectList'])->name('admin.clients.selectList');
   
    Route::post('/admin/project_progress/store', [ProjectProgressController::class, 'store'])->name('admin.project_progress.store');
    Route::get('/admin/project_progress/list', [ProjectProgressController::class, 'list'])->name('admin.project_progress.list');
    Route::get('/admin/project_progress/edit/{project_progress_id}', [ProjectProgressController::class, 'edit'])->name('admin.project_progress.edit');
    Route::post('/admin/project_progress/update/{project_progress_id}', [ProjectProgressController::class, 'update'])->name('admin.project_progress.update');
    Route::post('/admin/project_progress/delete/{project_progress_id}', [ProjectProgressController::class, 'destroy'])->name('admin.project_progress.destroy');

    Route::get('/dashboard/data', [DashboardController::class, 'index'])->name('admin.dashboard.data');
});

Route::middleware('auth')->group(function () {
    Route::get('/change-password', [PasswordChangeController::class, 'show'])->name('password.change.form');
    Route::post('/change-password', [PasswordChangeController::class, 'update'])->name('password.change.update');

    Route::get('/projects/calendar-data', [ProjectController::class, 'getCalendarData']);

    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.api.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
