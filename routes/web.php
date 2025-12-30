<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

//Main route for the SPA application
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('home');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'getElement'])->name('projects.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});

//Admin section
Route::middleware(['auth', 'role:admin|superadmin'])->prefix('admin')->group(function () {
    Route::post('users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::post('roles', [UserManagementController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('users/{user}/role', [UserManagementController::class, 'updateUserRole'])->name('admin.users.updateRole');
    Route::post('users/{user}/permissions', [UserManagementController::class, 'updateUserPermissions'])->name('admin.users.updatePermissions');
});

require __DIR__.'/auth.php';
