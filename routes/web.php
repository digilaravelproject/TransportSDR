<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ManagePlansController;
use App\Http\Controllers\Admin\ManageSubscriptionsController;
use App\Http\Controllers\Admin\ManageShiftsController;
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
        
        Route::get('/subscriptions/statistics', [ManageSubscriptionsController::class, 'statistics'])->name('admin.subscriptions.statistics');
        Route::get('/subscriptions/export', [ManageSubscriptionsController::class, 'export'])->name('admin.subscriptions.export');
        Route::post('/subscriptions/{subscription}/cancel', [ManageSubscriptionsController::class, 'cancel'])->name('admin.subscriptions.cancel');
        Route::post('/subscriptions/{subscription}/renew', [ManageSubscriptionsController::class, 'renew'])->name('admin.subscriptions.renew');
        Route::resource('/subscriptions', ManageSubscriptionsController::class, ['as' => 'admin']);
        
        Route::resource('/shifts', ManageShiftsController::class, ['as' => 'admin']);
        Route::post('/shifts/{shift}/add-driver', [ManageShiftsController::class, 'addDriver'])->name('admin.shifts.add-driver');
        Route::post('/shifts/{shift}/remove-driver', [ManageShiftsController::class, 'removeDriver'])->name('admin.shifts.remove-driver');
    });
});
