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

        // MODULE 3 — Vehicle Maintenance — routes update karo
        Route::apiResource('vehicles', Api\VehicleController::class);
        Route::prefix('vehicles')->group(function () {
            Route::get('documents/expiring',         [Api\VehicleController::class, 'expiringDocuments']);
            Route::prefix('{vehicle}')->group(function () {
                Route::post('fuel',                  [Api\VehicleController::class, 'addFuel']);
                Route::post('maintenance',           [Api\VehicleController::class, 'addMaintenance']);
                Route::post('document',              [Api\VehicleController::class, 'addDocument']);
                Route::post('spare-part',            [Api\VehicleController::class, 'addSparePart']);
                Route::get('report',                 [Api\VehicleController::class, 'report']);
                Route::get('history',                [Api\VehicleController::class, 'history']);
                Route::get('gps',                    [Api\VehicleController::class, 'gpsLocation']);
            });
        });

        // MODULE 4 — Staff Management
        Route::apiResource('staff', Api\StaffController::class);
        Route::prefix('staff')->group(function () {
            Route::get('performance',                        [Api\StaffController::class, 'performance']);
            Route::prefix('{staff}')->group(function () {
                // Attendance
                Route::post('attendance',                    [Api\StaffController::class, 'markAttendance']);
                Route::get('attendance',                     [Api\StaffController::class, 'attendanceList']);

                // DA
                Route::post('da',                            [Api\StaffController::class, 'calculateDA']);
                Route::get('da',                             [Api\StaffController::class, 'daList']);

                // Advance
                Route::post('advance',                       [Api\StaffController::class, 'giveAdvance']);
                Route::get('advances',                       [Api\StaffController::class, 'advanceList']);

                // Salary
                Route::post('salary/generate',               [Api\StaffController::class, 'generateSalary']);
                Route::get('salary',                         [Api\StaffController::class, 'salaryList']);
                Route::post('salary/{salary}/pay',           [Api\StaffController::class, 'paySalary']);
                Route::get('salary/{salary}/slip',           [Api\StaffController::class, 'salarySlip']);

                // Documents
                Route::post('document',                      [Api\StaffController::class, 'uploadDocument']);

                // Trips
                Route::get('trips',                          [Api\StaffController::class, 'tripHistory']);
            });
        });
        // MODULE 5 — Corporate / Company Duty Management
        Route::prefix('corporate')->group(function () {
            Route::get('/',                                          [Api\CorporateController::class, 'index']);
            Route::post('/',                                         [Api\CorporateController::class, 'store']);
            Route::get('/{corporate}',                               [Api\CorporateController::class, 'show']);
            Route::put('/{corporate}',                               [Api\CorporateController::class, 'update']);
            Route::delete('/{corporate}',                            [Api\CorporateController::class, 'destroy']);

            Route::prefix('/{corporate}')->group(function () {
                // Duties
                Route::get('duties',                                 [Api\CorporateController::class, 'duties']);
                Route::post('duty',                                  [Api\CorporateController::class, 'addDuty']);
                Route::patch('duty/{duty}',                          [Api\CorporateController::class, 'updateDuty']);

                // Fines
                Route::get('fines',                                  [Api\CorporateController::class, 'fines']);
                Route::post('fine',                                  [Api\CorporateController::class, 'addFine']);
                Route::patch('fine/{fine}/waive',                    [Api\CorporateController::class, 'waiveFine']);

                // Invoice
                Route::post('generate-invoice',                      [Api\CorporateController::class, 'generateInvoice']);
                Route::get('invoice/{payment}',                      [Api\CorporateController::class, 'downloadInvoice']);

                // Payments
                Route::get('payments',                               [Api\CorporateController::class, 'payments']);
                Route::post('payment/{payment}/pay',                 [Api\CorporateController::class, 'recordPayment']);

                // Report
                Route::get('report',                                 [Api\CorporateController::class, 'report']);
            });
        });

        // MODULE 6 — Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('summary',       [Api\DashboardController::class, 'summary']);
            Route::get('charts',        [Api\DashboardController::class, 'charts']);
            Route::get('pl-report',     [Api\DashboardController::class, 'plReport']);
            Route::get('performance',   [Api\DashboardController::class, 'performance']);
            Route::get('notifications', [Api\DashboardController::class, 'notifications']);
            Route::post('clear-cache',  [Api\DashboardController::class, 'clearCache']);
        });
    });
