<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;

//Main route for the SPA application
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');


Route::middleware('auth')->group(function () {
    //Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Team routes
    Route::post('/teams/switch/{team}', [TeamController::class, 'switch'])->name('teams.switch');
    Route::resource('teams', TeamController::class)->only(['store', 'update', 'destroy']);
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.remove');
    Route::post('/teams/invite', [TeamController::class, 'addMember'])->name('teams.invite');
    Route::put('/teams/{team}/members/roles', [TeamController::class, 'updateMemberRole'])->name('teams.members.updateRole');

    //Project routes
    Route::post('/project', [ProjectController::class, 'store'])->name('project.store');
    Route::get('/project/{project}', [ProjectController::class, 'getElement'])->name('project.show');
    Route::put('/project/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::delete('/project/{project}', [ProjectController::class, 'destroy'])->name('project.destroy');
});

//Accept invitation route
Route::get('/invitation/accept/{token}', [TeamController::class, 'acceptInvitation'])->name('teams.accept');

//Admin section
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->group(function () {
    Route::post('users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::post('roles', [UserManagementController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('users/{user}/role', [UserManagementController::class, 'updateUserRole'])->name('admin.users.updateRole');
    Route::post('users/{user}/permissions', [UserManagementController::class, 'updateUserPermissions'])->name('admin.users.updatePermissions');
});

require __DIR__.'/auth.php';
