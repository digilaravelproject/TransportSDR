<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;
use App\Http\Controllers\SuperAdmin;

// ── Public ─────────────────────────────────────────────────────



// Registration — 2 step
Route::post('/auth/register/send-otp',   [Api\AuthController::class, 'registerSendOtp']);
Route::post('/auth/register/verify',     [Api\AuthController::class, 'registerVerify']);
Route::post('/auth/register/resend-otp', [Api\AuthController::class, 'registerResendOtp']);


// Route::post('/auth/login', [Api\AuthController::class, 'login']);
Route::post('/auth/signup',          [Api\AuthController::class, 'signup']);
Route::post('/auth/send-otp',        [Api\AuthController::class, 'sendLoginOtp']);
Route::post('/auth/verify-login-otp', [Api\AuthController::class, 'verifyLoginOtp']);
Route::post('/auth/resend-login-otp', [Api\AuthController::class, 'resendLoginOtp']);

// Forgot Password 3-step
Route::post('/auth/forgot-password',  [Api\AuthController::class, 'forgotPassword']);
Route::post('/auth/verify-forgot-otp', [Api\AuthController::class, 'verifyForgotOtp']);
Route::post('/auth/reset-password',   [Api\AuthController::class, 'resetPassword']);
// Plans Management - Specific routes BEFORE resource routing
Route::get('plans/stats/total', [Api\PlanController::class, 'getTotalPlans']);
Route::get('plans/list', [Api\PlanController::class, 'getTotalPlansList']);
Route::apiResource('plans', Api\PlanController::class);


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

        Route::prefix('document-templates')->group(function () {
            Route::get('/categories',    [Api\DocumentTemplateController::class, 'categories']);
            Route::get('/',              [Api\DocumentTemplateController::class, 'index']);
            Route::post('/submit',       [Api\DocumentTemplateController::class, 'submit']);
            Route::get('/{documentTemplate}', [Api\DocumentTemplateController::class, 'show']);
        });

        Route::apiResource('roles', Api\RoleController::class);

        // Auth
        Route::post('auth/logout', [Api\AuthController::class, 'logout']);
        Route::get('auth/me',      [Api\AuthController::class, 'me']);
        Route::post('auth/profile/update',   [Api\AuthController::class, 'updateProfile']);
        // Shift Drivers Management
        Route::get('drivers', [Api\ShiftController::class, 'driversList']);
        Route::get('drivers/{shift_id}', [Api\ShiftController::class, 'availableDrivers']);
        Route::post('shifts/{shift}/add-driver', [Api\ShiftController::class, 'addDriver']);
        Route::post('shifts/{shift}/remove-driver', [Api\ShiftController::class, 'removeDriver']);

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
        // Additional vehicle endpoints
        Route::get('vehicles/stats', [Api\VehicleController::class, 'stats']);
        Route::get('vehicles/list', [Api\VehicleController::class, 'list']);
        Route::get('vehicles/search', [Api\VehicleController::class, 'search']);
        Route::get('vehicles/filters', [Api\VehicleController::class, 'filter']);
        Route::apiResource('vehicles', Api\VehicleController::class);
        Route::get('routes/{route_id}/available-vehicles', [Api\VehicleController::class, 'availableVehicles']);
        Route::prefix('vehicles')->group(function () {
            Route::get('documents/expiring',         [Api\VehicleController::class, 'expiringDocuments']);
            Route::prefix('{vehicle}')->group(function () {
                // New unified activity endpoints
                Route::post('activity/fuel',    [Api\VehicleActivityController::class, 'storeFuel']);
                Route::get('activity/fuel',     [Api\VehicleActivityController::class, 'fuelHistory']);

                Route::post('activity/service', [Api\VehicleActivityController::class, 'storeService']);
                Route::get('activity/service',  [Api\VehicleActivityController::class, 'serviceHistory']);

                Route::post('activity/repair',  [Api\VehicleActivityController::class, 'storeRepair']);
                Route::get('activity/repair',   [Api\VehicleActivityController::class, 'repairHistory']);

                // Documents and timeline
                Route::get('documents',         [Api\VehicleActivityController::class, 'documents']);
                Route::get('timeline',          [Api\VehicleActivityController::class, 'timeline']);

                // Keep vehicle report and gps endpoints
                Route::get('report',            [Api\VehicleController::class, 'report']);
                Route::get('gps',               [Api\VehicleController::class, 'gpsLocation']);
            });
        });

        // MODULE 4 — Staff Management

        Route::get('roles', [Api\RoleController::class, 'indexrole']);

        // Staff Module

        // Route::prefix('staff/{staff}')->group(function () {
        //     Route::get('performance-report', [Api\StaffController::class, 'getPerformance']);
        //     Route::get('duty-logs', [Api\StaffController::class, 'dutyHistory']);
        //     Route::post('attendance', [Api\StaffController::class, 'markAttendance']);
        //     Route::post('advance', [Api\StaffController::class, 'giveAdvance']);
        //     Route::get('advances', [Api\StaffController::class, 'advanceList']);
        //     // Route::post('salary/pay', [Api\StaffController::class, 'paySalary']);
        //     Route::post('document', [Api\StaffController::class, 'uploadDocument']);
        // });

        Route::apiResource('staff', Api\StaffController::class);
        Route::post('staff/salary/filter', [Api\StaffController::class, 'salaryFilter']);
        Route::prefix('staff')->group(function () {
            Route::get('performance',                        [Api\StaffController::class, 'performance']);
            Route::prefix('{staff}')->group(function () {
                // Salary Filter/Search with POST

                Route::get('performance-report', [Api\StaffController::class, 'getPerformance']);
                Route::get('duty-logs', [Api\StaffController::class, 'dutyHistory']);

                Route::get('documents', [Api\StaffController::class, 'documents']);

                Route::post('document', [Api\StaffController::class, 'uploadDocument']);
                // Advance
                Route::post('advance',                       [Api\StaffController::class, 'giveAdvance']);
                Route::get('advances',                       [Api\StaffController::class, 'advanceList']);
                // Attendance
                Route::post('attendance',                    [Api\StaffController::class, 'markAttendance']);
                Route::get('attendance',                     [Api\StaffController::class, 'attendanceList']);

                // DA
                Route::post('da',                            [Api\StaffController::class, 'calculateDA']);
                Route::get('da',                             [Api\StaffController::class, 'daList']);



                // Salary
                Route::post('salary/generate',               [Api\StaffController::class, 'generateSalary']);
                Route::get('salary',                         [Api\StaffController::class, 'salaryList']);
                Route::post('salary/{salary}/pay',           [Api\StaffController::class, 'paySalary']);
                Route::get('salary/{salary}/slip',           [Api\StaffController::class, 'salarySlip']);

                // Documents
                // Route::post('document',                      [Api\StaffController::class, 'uploadDocument']);

                // Trips
                Route::get('trips',                          [Api\StaffController::class, 'tripHistory']);
            });
        });
        // MODULE 5 — Corporate / Company Duty Management
        Route::prefix('vendors')->group(function () {
            Route::post('/', [Api\VendorController::class, 'store']);
            Route::get('/', [Api\VendorController::class, 'index']);
            Route::get('/{vendor}', [Api\VendorController::class, 'show']);
            Route::get('/{vendor}/available-vehicles', [Api\VendorController::class, 'availableVehicles']);
            Route::post('/{vendor}/assign-vehicles', [Api\VendorController::class, 'assignVehicles']);
            Route::delete('/{vendor}/remove-vehicle/{vehicle}', [Api\VendorController::class, 'removeVehicle']);
            Route::post('/{vendor}/bills', [Api\VendorController::class, 'addBill']);
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

        // MODULE 7 — Templates
        Route::prefix('templates')->group(function () {
            Route::get('/',                                   [Api\TemplateController::class, 'index']);

            // Invoice
            Route::post('invoice/trip/{trip}',                [Api\TemplateController::class, 'generateTripInvoice']);

            // Letterhead
            Route::post('letterhead',                         [Api\TemplateController::class, 'generateLetterhead']);

            // Quotation
            Route::post('quotation/lead/{lead}',              [Api\TemplateController::class, 'generateLeadQuotation']);
            Route::post('quotation/custom',                   [Api\TemplateController::class, 'generateCustomQuotation']);

            // E-Invoice
            Route::get('einvoice/payload/{trip}',             [Api\TemplateController::class, 'eInvoicePayload']);
            Route::post('einvoice/upload/{trip}',             [Api\TemplateController::class, 'uploadEInvoice']);
            Route::patch('einvoice/{log}/cancel',             [Api\TemplateController::class, 'cancelEInvoice']);

            // Download any log file
            Route::get('logs/{log}/download',                 [Api\TemplateController::class, 'download']);
        });

        // MODULE 8 — Cash Book Management
        Route::prefix('cashbook')->group(function () {
            // Balance
            Route::get('balance',                                    [Api\CashBookController::class, 'balance']);

            // Ledger
            Route::get('ledger',                                     [Api\CashBookController::class, 'ledger']);



            // Online payment tracker
            Route::get('online-payments',                            [Api\CashBookController::class, 'onlinePayments']);
            Route::post('online-payments',                           [Api\CashBookController::class, 'recordOnlinePayment']);
            Route::patch('online-payments/{payment}/status',         [Api\CashBookController::class, 'updateOnlinePaymentStatus']);
            Route::post('online-payments/{payment}/refund',          [Api\CashBookController::class, 'refundOnlinePayment']);

            // QR generation & management
            Route::get('qr',                                         [Api\CashBookController::class, 'listQr']);
            Route::post('qr/generate',                               [Api\CashBookController::class, 'generateQr']);
            Route::get('qr/{qr}',                                    [Api\CashBookController::class, 'showQr']);
            Route::patch('qr/{qr}/deactivate',                       [Api\CashBookController::class, 'deactivateQr']);
            Route::post('qr/{qr}/send-alert',                        [Api\CashBookController::class, 'sendQrAlert']);

            // UPI deep link only
            Route::get('upi-link',                                   [Api\CashBookController::class, 'generateUpiLink']);


            // Cash book entries CRUD
            Route::get('/',                                          [Api\CashBookController::class, 'index']);
            Route::post('/',                                         [Api\CashBookController::class, 'store']);
            Route::get('/{entry}',                                   [Api\CashBookController::class, 'show']);
            Route::put('/{entry}',                                   [Api\CashBookController::class, 'update']);
            Route::delete('/{entry}',                                [Api\CashBookController::class, 'destroy']);
            Route::post('/{entry}/receipt',                          [Api\CashBookController::class, 'uploadReceipt']);
        });
        // MODULE 9 — Inventory Management
        Route::prefix('inventory')->group(function () {
            // Alerts & Reports (before resource routes)
            Route::get('alerts/low-stock',                          [Api\InventoryController::class, 'lowStockAlerts']);
            Route::get('valuation',                                 [Api\InventoryController::class, 'valuation']);

            // Categories
            Route::get('categories',                                [Api\InventoryController::class, 'categories']);
            Route::post('categories',                               [Api\InventoryController::class, 'createCategory']);

            // Transaction document upload
            Route::post('transactions/{transaction}/document',      [Api\InventoryController::class, 'uploadDocument']);

            // Item CRUD
            Route::get('/',                                         [Api\InventoryController::class, 'index']);
            Route::post('/',                                        [Api\InventoryController::class, 'store']);
            Route::get('/{item}',                                   [Api\InventoryController::class, 'show']);
            Route::put('/{item}',                                   [Api\InventoryController::class, 'update']);
            Route::delete('/{item}',                                [Api\InventoryController::class, 'destroy']);

            // Stock operations
            Route::post('/{item}/stock-in',                         [Api\InventoryController::class, 'stockIn']);
            Route::post('/{item}/stock-out',                        [Api\InventoryController::class, 'stockOut']);
            Route::post('/{item}/adjust',                           [Api\InventoryController::class, 'adjust']);
            Route::post('/{item}/return',                           [Api\InventoryController::class, 'returnStock']);
            Route::post('/{item}/damage',                           [Api\InventoryController::class, 'markDamaged']);
            Route::get('/{item}/history',                           [Api\InventoryController::class, 'history']);
        });

        // MODULE 10 — Subscriptions
        Route::prefix('subscriptions')->group(function () {
            Route::get('current', [Api\SubscriptionController::class, 'current']);
            Route::get('stats', [Api\SubscriptionController::class, 'stats']);
            Route::get('/', [Api\SubscriptionController::class, 'index']);
            Route::post('/', [Api\SubscriptionController::class, 'store']);
            Route::post('verify-payment', [Api\SubscriptionController::class, 'verifyPayment']);
            Route::get('{id}', [Api\SubscriptionController::class, 'show']);
            Route::post('{id}/cancel', [Api\SubscriptionController::class, 'cancel']);
            Route::post('{id}/pause', [Api\SubscriptionController::class, 'pause']);
            Route::post('{id}/resume', [Api\SubscriptionController::class, 'resume']);
        });

        // MODULE 11 — Shifts Management
        Route::prefix('shifts')->group(function () {
            Route::get('search', [Api\ShiftController::class, 'search']);
            Route::get('stats', [Api\ShiftController::class, 'stats']);
            Route::get('list', [Api\ShiftController::class, 'list']);
            Route::get('/', [Api\ShiftController::class, 'index']);
            Route::post('/', [Api\ShiftController::class, 'store']);
            Route::get('{id}', [Api\ShiftController::class, 'show']);
            Route::put('{id}', [Api\ShiftController::class, 'update']);
            Route::delete('{id}', [Api\ShiftController::class, 'destroy']);
        });
        // ── Route Management ──
        Route::get('routes', [Api\RouteController::class, 'index']);
        Route::post('routes', [Api\RouteController::class, 'store']);
        Route::get('routes/search', [Api\RouteController::class, 'search']);
        Route::get('routes/{id}', [Api\RouteController::class, 'show']);
        Route::put('routes/{id}', [Api\RouteController::class, 'update']);
        Route::post('routes/{id}/assign-vehicles', [Api\RouteController::class, 'assignVehicles']);

        // Staff Attendance
        Route::get('attendance', [Api\AttendanceController::class, 'index']);
        Route::post('attendance', [Api\AttendanceController::class, 'store']);
        Route::get('attendance/staff/{staff}', [Api\AttendanceController::class, 'staffRecords']);
        Route::get('attendance/search', [Api\AttendanceController::class, 'search']);
    });
