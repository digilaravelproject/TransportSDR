<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ManagePlansController;
use App\Http\Controllers\Admin\ManageSubscriptionController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        Route::resource('/users', AdminUserController::class, ['as' => 'admin']);
        
        Route::resource('/plans', ManagePlansController::class, ['as' => 'admin']);
        
        Route::get('/subscriptions', [ManageSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    });
});
