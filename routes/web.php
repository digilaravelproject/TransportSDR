<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ManagePlansController;
use App\Http\Controllers\Admin\ManageSubscriptionsController;
use App\Http\Controllers\Admin\ManageShiftsController;
use App\Http\Controllers\Admin\TemplateCategoryController;
use App\Http\Controllers\Admin\DocumentTemplateController;

Route::get('/', function () {
    return view('welcome');
});
Route::prefix('admin')->name('admin.')->group(function () {

    // Main Staff CRUD
    Route::resource('staff', App\Http\Controllers\Admin\StaffController::class);

    // Extra Web Actions for Staff Profile Page
    Route::prefix('staff/{staff}')->name('staff.')->group(function () {
        Route::post('attendance', [App\Http\Controllers\Admin\StaffController::class, 'markAttendance'])->name('attendance');
        Route::post('advance', [App\Http\Controllers\Admin\StaffController::class, 'giveAdvance'])->name('advance');
        Route::post('document', [App\Http\Controllers\Admin\StaffController::class, 'uploadDocument'])->name('document');
        Route::post('toggle-status', [App\Http\Controllers\Admin\StaffController::class, 'toggleStatus'])->name('toggle-status');
    });
});
Route::prefix('admin')->name('admin.')->group(function () {

    Route::resource('template-categories', TemplateCategoryController::class);
    Route::resource('document-templates', DocumentTemplateController::class);

    // Preview PDF
    Route::get(
        'document-templates/{documentTemplate}/preview',
        [DocumentTemplateController::class, 'preview']
    )->name('document-templates.preview');

    // Toggle Status
    Route::patch(
        'document-templates/{documentTemplate}/toggle-status',
        [DocumentTemplateController::class, 'toggleStatus']
    )->name('document-templates.toggle-status');
});

Route::prefix('admin')->name('admin.')->group(function () {


    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('/users', AdminUserController::class);

        // Admin Vehicle & Vendor Management
        Route::resource('/vehicles', App\Http\Controllers\Admin\AdminVehicleController::class);
        Route::resource('/vendors', App\Http\Controllers\Admin\AdminVendorController::class);

        Route::resource('/plans', ManagePlansController::class);

        Route::get('/subscriptions/statistics', [ManageSubscriptionsController::class, 'statistics'])->name('subscriptions.statistics');
        Route::get('/subscriptions/export', [ManageSubscriptionsController::class, 'export'])->name('subscriptions.export');
        Route::post('/subscriptions/{subscription}/cancel', [ManageSubscriptionsController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('/subscriptions/{subscription}/renew', [ManageSubscriptionsController::class, 'renew'])->name('subscriptions.renew');
        Route::resource('/subscriptions', ManageSubscriptionsController::class);

        Route::resource('/shifts', ManageShiftsController::class);
        Route::post('/shifts/{shift}/add-driver', [ManageShiftsController::class, 'addDriver'])->name('shifts.add-driver');
        Route::post('/shifts/{shift}/remove-driver', [ManageShiftsController::class, 'removeDriver'])->name('shifts.remove-driver');

        // Manage Routes
        Route::resource('/routes', App\Http\Controllers\Admin\ManageRoutesController::class);
        // Leads management (Admin)
        Route::resource('/leads', App\Http\Controllers\Admin\AdminLeadController::class);
        Route::post('/leads/{lead}/assign-vehicle', [App\Http\Controllers\Admin\AdminLeadController::class, 'assignVehicle'])->name('leads.assign-vehicle');
        Route::post('/leads/{lead}/assign-driver', [App\Http\Controllers\Admin\AdminLeadController::class, 'assignDriver'])->name('leads.assign-driver');
        Route::post('/routes/{route}/add-vehicle', [App\Http\Controllers\Admin\ManageRoutesController::class, 'addVehicle'])->name('routes.add-vehicle');
        Route::post('/routes/{route}/remove-vehicle', [App\Http\Controllers\Admin\ManageRoutesController::class, 'removeVehicle'])->name('routes.remove-vehicle');
        // Manage Modules
        Route::resource('/modules', App\Http\Controllers\Admin\ModuleController::class);
        // Vehicle Types (Admin)
        Route::resource('/vehicle-types', App\Http\Controllers\Admin\ManageVehicleTypeController::class);
        // Finance management
        Route::get('/finance', [App\Http\Controllers\Admin\FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/{entry}', [App\Http\Controllers\Admin\FinanceController::class, 'show'])->name('finance.show');
        Route::delete('/finance/{entry}', [App\Http\Controllers\Admin\FinanceController::class, 'destroy'])->name('finance.destroy');

        // Inventory management (new)
        Route::get('/inventory', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{inventory}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('inventory.show');
        Route::delete('/inventory/{inventory}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('inventory.destroy');
    });
});
