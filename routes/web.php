<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\UserManagementController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

Route::get('/home', function () {
    return Inertia::render('Home');
})->middleware(['auth', 'verified'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin|superadmin'])->prefix('admin')->group(function () {
    Route::get('users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('users', [UserManagementController::class, 'storeUser'])->name('admin.users.store');
    Route::post('roles', [UserManagementController::class, 'storeRole'])->name('admin.roles.store');
    Route::post('users/{user}/role', [UserManagementController::class, 'updateUserRole'])->name('admin.users.updateRole');
    Route::post('users/{user}/permissions', [UserManagementController::class, 'updateUserPermissions'])->name('admin.users.updatePermissions');
});

Route::middleware(['permission:manage users|manage roles'])->get('/api/admin/perm-management-data', function (Request $request) {
    return response()->json([
        'users' => \App\Models\User::with('roles', 'permissions')->get(),
        'roles' => \Spatie\Permission\Models\Role::all(),
        'permissions' => \Spatie\Permission\Models\Permission::all(),
    ]);
});

require __DIR__.'/auth.php';
