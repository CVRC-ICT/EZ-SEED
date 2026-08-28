<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ForcePasswordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Forced Password Change (any authenticated user, not admin-only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/password/force-change', [ForcePasswordController::class, 'edit'])
        ->name('password.force.edit');

    Route::put('/password/force-change', [ForcePasswordController::class, 'update'])
        ->name('password.force.update');
});

/*
|--------------------------------------------------------------------------
| Administrator-only routes
|--------------------------------------------------------------------------
| Protected by both 'auth' and 'admin' middleware. A normal User hitting
| any of these gets a 403 (see EnsureUserIsAdministrator middleware).
*/
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('/users/{user}/reactivate', [UserManagementController::class, 'reactivate'])->name('users.reactivate');
        Route::patch('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    });
