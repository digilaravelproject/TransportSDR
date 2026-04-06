<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;
use App\Http\Controllers\SuperAdmin;

// ── Public ─────────────────────────────────────────────────────
// Route::post('/auth/login', [Api\AuthController::class, 'login']);
Route::post('/auth/send-otp',        [Api\AuthController::class, 'sendLoginOtp']);
Route::post('/auth/verify-login-otp', [Api\AuthController::class, 'verifyLoginOtp']);
Route::post('/auth/resend-login-otp', [Api\AuthController::class, 'resendLoginOtp']);

// Forgot Password 3-step
Route::post('/auth/forgot-password',  [Api\AuthController::class, 'forgotPassword']);
Route::post('/auth/verify-forgot-otp', [Api\AuthController::class, 'verifyForgotOtp']);
Route::post('/auth/reset-password',   [Api\AuthController::class, 'resetPassword']);

// ── Super Admin ─────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'superadmin'])
    ->prefix('super-admin')
    ->group(function () {
        Route::get('stats',                          [SuperAdmin\TenantController::class, 'stats']);
        Route::apiResource('tenants',                 SuperAdmin\TenantController::class);
        Route::patch('tenants/{tenant}/suspend',     [SuperAdmin\TenantController::class, 'suspend']);
        Route::patch('tenants/{tenant}/activate',    [SuperAdmin\TenantController::class, 'activate']);
        Route::post('tenants/{tenant}/users',        [SuperAdmin\TenantController::class, 'createUser']);
    });


Route::middleware(['auth:sanctum', 'tenant'])
    ->prefix('v1')
    ->group(function () {

        // Auth
        Route::post('auth/logout', [Api\AuthController::class, 'logout']);
        Route::get('auth/me',      [Api\AuthController::class, 'me']);

        // MODULE 1 — Trip Management
        Route::apiResource('trips', Api\TripController::class);
        Route::prefix('trips/{trip}')->group(function () {
            Route::patch('km',        [Api\TripController::class, 'updateKm']);
            Route::patch('status',    [Api\TripController::class, 'updateStatus']);
            Route::post('payment',    [Api\TripController::class, 'addPayment']);
            Route::get('invoice',     [Api\TripController::class, 'invoice']);
            Route::get('duty-slip',   [Api\TripController::class, 'dutySlip']);
        });

        // Dropdowns for trip form
        Route::get('dropdowns/vehicles',  fn() => response()->json(['success' => true, 'data' => \App\Models\Vehicle::available()->get(['id', 'registration_number', 'type', 'seating_capacity'])]));
        Route::get('dropdowns/drivers',   fn() => response()->json(['success' => true, 'data' => \App\Models\Staff::drivers()->available()->get(['id', 'name', 'phone'])]));
        Route::get('dropdowns/helpers',   fn() => response()->json(['success' => true, 'data' => \App\Models\Staff::helpers()->available()->get(['id', 'name', 'phone'])]));
        Route::get('dropdowns/customers', fn() => response()->json(['success' => true, 'data' => \App\Models\Customer::where('is_active', true)->get(['id', 'name', 'phone'])]));

        // ────────────────────────────────────────────────────────
        // MODULE 2 — Leads / Enquiry Management
        // ────────────────────────────────────────────────────────
        Route::prefix('leads')->group(function () {
            Route::get('/',                      [Api\LeadController::class, 'index']);
            Route::post('/',                     [Api\LeadController::class, 'store']);
            Route::get('/stats',                 [Api\LeadController::class, 'stats']);
            Route::get('/{lead}',                [Api\LeadController::class, 'show']);
            Route::put('/{lead}',                [Api\LeadController::class, 'update']);
            Route::delete('/{lead}',             [Api\LeadController::class, 'destroy']);
            Route::patch('/{lead}/status',       [Api\LeadController::class, 'updateStatus']);
            Route::post('/{lead}/convert',       [Api\LeadController::class, 'convert']);
            Route::get('/{lead}/quotation',    [Api\LeadController::class, 'quotation']);
            Route::get('/{lead}/bill',         [Api\LeadController::class, 'bill']);
        });
    });
