<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    // Auth & Core Pages
    AuthController,
    DashboardController,
    PageController,
    ProfileController,
    SettingsController,
    
    // Phase 1: Core Models
    BuildingController,
    BuildingPhotoController,
    FloorPlanController,
    UtilityTypeController,
    TenantController,
    VendorController,
    MaintenanceCategoryController,
    
    // Phase 2: Dependent Models
    UnitController,
    MeterReadingController,
    MaintenanceRequestController,
    
    // Phase 3: Billing Models
    LeaseController,
    BillController,
    PaymentMethodController,
    PaymentController,
    
    // Phase 4: Specialized Models
    ElectricityReadingController,
    WaterReadingController,
    GasReadingController,
    ConsumptionController,
    BillItemController,
    PreventiveMaintenanceController,
    RepairController,
    
    // Phase 5: Support Models
    AlertRuleController,
    AlertController,
    RateScheduleController,
    RateController,
    TaxJurisdictionController,
    TaxController,
    ReportController,
    RoleController,
    PermissionController,
    ActivityLogController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Home & Landing Page
Route::get('/', [PageController::class, 'home'])->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');
});

// Logout (accessible for authenticated users)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware(['auth'])->group(function () {
    
    // ========================================================================
    // DASHBOARD & USER MANAGEMENT
    // ========================================================================
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('/dashboard/charts', [DashboardController::class, 'charts'])->name('dashboard.charts');
    
    // User Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::delete('/photo', [ProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::get('/activity', [ProfileController::class, 'activity'])->name('activity');
        Route::get('/notifications', [ProfileController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [ProfileController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/devices', [ProfileController::class, 'devices'])->name('devices');
        Route::delete('/devices/{tokenId}', [ProfileController::class, 'revokeDevice'])->name('devices.revoke');
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::post('/two-factor/enable', [ProfileController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::post('/two-factor/disable', [ProfileController::class, 'disableTwoFactor'])->name('two-factor.disable');
    });
    
    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
        Route::put('/billing', [SettingsController::class, 'updateBilling'])->name('billing.update');
        Route::put('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::put('/maintenance', [SettingsController::class, 'updateMaintenance'])->name('maintenance.update');
        Route::put('/system', [SettingsController::class, 'updateSystem'])->name('system.update');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
        Route::post('/run-migrations', [SettingsController::class, 'runMigrations'])->name('run-migrations');
        Route::post('/generate-key', [SettingsController::class, 'generateKey'])->name('generate-key');
    });
    
    // ========================================================================
    // PHASE 1: CORE RESOURCES
    // ========================================================================
    
    // Buildings
    Route::resource('buildings', BuildingController::class);
    Route::post('/buildings/{id}/restore', [BuildingController::class, 'restore'])->name('buildings.restore');
    Route::prefix('buildings/{building}')->name('buildings.')->group(function () {
        Route::get('/units', [BuildingController::class, 'units'])->name('units');
        Route::get('/floor-plans', [BuildingController::class, 'floorPlans'])->name('floor-plans');
        Route::get('/maintenance', [BuildingController::class, 'maintenance'])->name('maintenance');
        Route::get('/reports', [BuildingController::class, 'reports'])->name('reports');
        Route::get('/export', [BuildingController::class, 'export'])->name('export');
        Route::get('/stats', [BuildingController::class, 'stats'])->name('stats');
        Route::get('/statistics', [BuildingController::class, 'statistics'])->name('statistics');
        // Tenant-related building routes
        Route::get('/tenants', [BuildingController::class, 'tenants'])->name('tenants');
        Route::get('/tenants/active', [BuildingController::class, 'activeTenants'])->name('tenants.active');
        Route::get('/tenants/expiring', [BuildingController::class, 'expiringLeases'])->name('tenants.expiring');
    });
    
    // Building Photo Routes
    Route::prefix('buildings/{building}')->name('buildings.photos.')->group(function () {
        Route::post('/photos/upload', [BuildingPhotoController::class, 'upload'])->name('upload');
        Route::post('/photos/{photo}/set-primary', [BuildingPhotoController::class, 'setPrimary'])->name('set-primary');
        Route::delete('/photos/{photo}', [BuildingPhotoController::class, 'destroy'])->name('destroy');
        Route::get('/photos', [BuildingPhotoController::class, 'index'])->name('index');
    });
    
    // Floor Plans
    Route::resource('floor-plans', FloorPlanController::class)->except(['store', 'update']);
    Route::post('buildings/{building}/floor-plans', [FloorPlanController::class, 'store'])->name('floor-plans.store');
    Route::put('buildings/{building}/floor-plans/{floor_plan}', [FloorPlanController::class, 'update'])->name('floor-plans.update');
    
    // Utility Types
    Route::resource('utility-types', UtilityTypeController::class);
    Route::prefix('utility-types/{utilityType}')->name('utility-types.')->group(function () {
        Route::get('/rates', [UtilityTypeController::class, 'rates'])->name('rates');
        Route::get('/consumptions', [UtilityTypeController::class, 'consumptions'])->name('consumptions');
    });
    
    // ========================================================================
    // TENANT ROUTES (FIXED - WITH REASSIGNMENT SUPPORT)
    // ========================================================================
    
    // Resource route for tenants
    Route::resource('tenants', TenantController::class);
    
    // Additional tenant routes
    Route::prefix('tenants')->name('tenants.')->group(function () {
        // Export
        Route::get('/export/csv', [TenantController::class, 'export'])->name('export');
        
        // Restore soft-deleted tenant
        Route::post('/{id}/restore', [TenantController::class, 'restore'])->name('restore')->withTrashed();
        
        // Force delete tenant
        Route::delete('/{id}/force-delete', [TenantController::class, 'forceDelete'])->name('force-delete')->withTrashed();
        
        // CRITICAL: Get units by building for tenant reassignment (AJAX)
        Route::get('/get-units/{buildingId}', [TenantController::class, 'getUnitsByBuilding'])
            ->name('get-units');
        
        // NEW: Check unit availability for tenant creation (AJAX)
        Route::get('/check-unit-availability', [TenantController::class, 'checkUnitAvailability'])
            ->name('check-unit-availability');
    });
    
    // Tenant detail routes
    Route::prefix('tenants/{tenant}')->name('tenants.')->group(function () {
        Route::get('/leases', [TenantController::class, 'leases'])->name('leases');
        Route::get('/bills', [TenantController::class, 'bills'])->name('bills');
        Route::get('/payments', [TenantController::class, 'payments'])->name('payments');
        Route::get('/payment-methods', [TenantController::class, 'paymentMethods'])->name('payment-methods');
        Route::get('/maintenance-requests', [TenantController::class, 'maintenanceRequests'])->name('maintenance-requests');
        Route::get('/contract', [TenantController::class, 'contract'])->name('contract');
        Route::get('/balance', [TenantController::class, 'balance'])->name('balance');
        Route::get('/history', [TenantController::class, 'history'])->name('history');
        Route::get('/lease-history', [TenantController::class, 'leaseHistory'])->name('lease-history');
    });
    
    // ========================================================================
    // ALTERNATIVE ROUTE FOR UNITS BY BUILDING (FOR REASSIGNMENT)
    // This is the route that the tenant edit form actually calls
    // ========================================================================
    Route::get('/buildings/{building}/units-json', function (App\Models\Building $building) {
        return response()->json(
            $building->units()
                ->select('id', 'unit_number', 'bedrooms', 'bathrooms', 'monthly_rent', 'floor', 'area', 'status')
                ->orderBy('unit_number')
                ->get()
        );
    })->middleware('auth')->name('buildings.units.json');
    
    // ========================================================================
    // VENDORS & MAINTENANCE CATEGORIES
    // ========================================================================
    
    // Vendors
    Route::resource('vendors', VendorController::class);
    Route::prefix('vendors/{vendor}')->name('vendors.')->group(function () {
        Route::get('/work-orders', [VendorController::class, 'workOrders'])->name('work-orders');
        Route::get('/performance', [VendorController::class, 'performance'])->name('performance');
        Route::get('/invoices', [VendorController::class, 'invoices'])->name('invoices');
        Route::get('/ratings', [VendorController::class, 'ratings'])->name('ratings');
        Route::get('/schedule', [VendorController::class, 'schedule'])->name('schedule');
    });
    
    // Maintenance Categories
    Route::resource('maintenance-categories', MaintenanceCategoryController::class);
    Route::prefix('maintenance-categories/{maintenanceCategory}')->name('maintenance-categories.')->group(function () {
        Route::get('/requests', [MaintenanceCategoryController::class, 'requests'])->name('requests');
        Route::get('/preventive-maintenances', [MaintenanceCategoryController::class, 'preventiveMaintenances'])->name('preventive-maintenances');
        Route::get('/vendors', [MaintenanceCategoryController::class, 'vendors'])->name('vendors');
    });
    
    // ========================================================================
    // PHASE 2: DEPENDENT RESOURCES
    // ========================================================================
    
    // FIRST: Define all custom unit routes (these must come BEFORE the resource route)
    Route::prefix('units')->name('units.')->group(function () {
        // Check duplicate unit number (AJAX) - MUST come before resource route
        Route::get('/check-duplicate', [UnitController::class, 'checkDuplicate'])->name('check-duplicate');
        
        // Get units by building for display with full details (AJAX)
        Route::get('/by-building/{buildingId}', [UnitController::class, 'getByBuilding'])->name('by-building');
        
        // Get units by building (simple list for dropdowns)
        Route::get('/get-units/{buildingId}', [UnitController::class, 'getUnitsByBuilding'])->name('get-units');
        
        // Export units
        Route::get('/export/csv', [UnitController::class, 'export'])->name('export');
        
        // Statistics
        Route::get('/statistics/overview', [UnitController::class, 'statistics'])->name('statistics');
        
        // Search
        Route::get('/search/results', [UnitController::class, 'search'])->name('search');
        
        // Batch refresh status for all units
        Route::post('/batch-refresh-status', [UnitController::class, 'batchRefreshStatus'])->name('batch-refresh-status');
        
        // Restore soft-deleted unit (with parameter)
        Route::post('/{id}/restore', [UnitController::class, 'restore'])->name('restore')->withTrashed();
        
        // Force delete unit (with parameter)
        Route::delete('/{id}/force-delete', [UnitController::class, 'forceDelete'])->name('force-delete')->withTrashed();
    });
    
    // THEN: Units Resource Route (this comes AFTER all custom routes)
    Route::resource('units', UnitController::class);
    
    // Unit detail routes
    Route::prefix('units/{unit}')->name('units.')->group(function () {
        Route::get('/leases', [UnitController::class, 'leases'])->name('leases');
        Route::get('/meter-readings', [UnitController::class, 'meterReadings'])->name('meter-readings');
        Route::get('/maintenance-requests', [UnitController::class, 'maintenanceRequests'])->name('maintenance-requests');
        Route::get('/preventive-maintenances', [UnitController::class, 'preventiveMaintenances'])->name('preventive-maintenances');
        Route::get('/bills', [UnitController::class, 'bills'])->name('bills');
        Route::get('/consumptions', [UnitController::class, 'consumptions'])->name('consumptions');
        Route::get('/history', [UnitController::class, 'history'])->name('history');
        Route::get('/occupancy', [UnitController::class, 'occupancy'])->name('occupancy');
        Route::get('/status', [UnitController::class, 'status'])->name('status');
        Route::get('/tenants', [UnitController::class, 'tenants'])->name('tenants');
        
        // Get tenants for a specific unit (AJAX)
        Route::get('/get-tenants', [UnitController::class, 'getTenants'])->name('get-tenants');
        
        // Update unit status
        Route::post('/update-status', [UnitController::class, 'updateStatus'])->name('update-status');
        
        // Refresh unit status from leases
        Route::post('/refresh-status', [UnitController::class, 'refreshStatus'])->name('refresh-status');
    });
    
    // Meter Readings
    Route::resource('meter-readings', MeterReadingController::class);
    Route::prefix('meter-readings')->name('meter-readings.')->group(function () {
        Route::get('/import', [MeterReadingController::class, 'importForm'])->name('import');
        Route::post('/import', [MeterReadingController::class, 'import']);
        Route::get('/bulk-entry', [MeterReadingController::class, 'bulkEntry'])->name('bulk-entry');
        Route::post('/bulk-entry', [MeterReadingController::class, 'bulkStore']);
        Route::get('/history/{unit}/{utilityType}', [MeterReadingController::class, 'history'])->name('history');
        Route::get('/export', [MeterReadingController::class, 'export'])->name('export');
    });
    Route::prefix('meter-readings/{meterReading}')->name('meter-readings.')->group(function () {
        Route::get('/electricity', [MeterReadingController::class, 'electricityReading'])->name('electricity');
        Route::get('/water', [MeterReadingController::class, 'waterReading'])->name('water');
        Route::get('/gas', [MeterReadingController::class, 'gasReading'])->name('gas');
        Route::get('/bill-item', [MeterReadingController::class, 'billItem'])->name('bill-item');
    });
    
    // ========================================================================
    // MAINTENANCE REQUESTS - COMPLETE ROUTES
    // ========================================================================
    
    // Maintenance Requests Resource (CRUD)
    Route::resource('maintenance-requests', MaintenanceRequestController::class);
    
    // Additional Maintenance Request Routes
    Route::prefix('maintenance-requests')->name('maintenance-requests.')->group(function () {
        // Statistics and overview
        Route::get('/statistics/overview', [MaintenanceRequestController::class, 'statistics'])->name('statistics');
        Route::get('/overdue/list', [MaintenanceRequestController::class, 'overdue'])->name('overdue');
        
        // Status-based filters
        Route::get('/open', [MaintenanceRequestController::class, 'open'])->name('open');
        Route::get('/assigned', [MaintenanceRequestController::class, 'assigned'])->name('assigned');
        Route::get('/in-progress', [MaintenanceRequestController::class, 'inProgress'])->name('in-progress');
        Route::get('/completed', [MaintenanceRequestController::class, 'completed'])->name('completed');
        
        // Calendar view
        Route::get('/calendar', [MaintenanceRequestController::class, 'calendar'])->name('calendar');
        
        // Export
        Route::get('/export', [MaintenanceRequestController::class, 'export'])->name('export');
    });
    
    // Single Maintenance Request Actions
    Route::prefix('maintenance-requests/{maintenanceRequest}')->name('maintenance-requests.')->group(function () {
        // Assignment forms and actions
        Route::get('/assign', [MaintenanceRequestController::class, 'assignForm'])->name('assign');
        Route::post('/assign-vendor', [MaintenanceRequestController::class, 'assignVendor'])->name('assign-vendor');
        
        // Status updates
        Route::post('/update-status', [MaintenanceRequestController::class, 'updateStatus'])->name('update-status');
        
        // Completion forms and actions
        Route::get('/complete', [MaintenanceRequestController::class, 'completeForm'])->name('complete');
        Route::post('/complete', [MaintenanceRequestController::class, 'complete']);
        
        // Cancellation
        Route::post('/cancel', [MaintenanceRequestController::class, 'cancel'])->name('cancel');
        
        // Feedback forms and actions
        Route::get('/feedback', [MaintenanceRequestController::class, 'feedback'])->name('feedback');
        Route::post('/feedback', [MaintenanceRequestController::class, 'addFeedback'])->name('feedback.submit');
        
        // Notes
        Route::post('/add-note', [MaintenanceRequestController::class, 'addNote'])->name('add-note');
        
        // Related data
        Route::get('/repairs', [MaintenanceRequestController::class, 'repairs'])->name('repairs');
        Route::get('/timeline', [MaintenanceRequestController::class, 'timeline'])->name('timeline');
        
        // Restore (for soft deletes)
        Route::post('/restore', [MaintenanceRequestController::class, 'restore'])->name('restore');
    });
    
    // ========================================================================
    // PHASE 3: BILLING RESOURCES
    // ========================================================================
    
    // Leases
    Route::resource('leases', LeaseController::class);
    Route::prefix('leases')->name('leases.')->group(function () {
        Route::get('/expiring', [LeaseController::class, 'expiring'])->name('expiring');
        Route::get('/active', [LeaseController::class, 'active'])->name('active');
        Route::get('/terminated', [LeaseController::class, 'terminated'])->name('terminated');
        Route::get('/renewals', [LeaseController::class, 'renewals'])->name('renewals');
        Route::get('/export', [LeaseController::class, 'export'])->name('export');
        Route::get('/get-units/{buildingId}', [LeaseController::class, 'getUnitsByBuilding'])->name('get-units');
    });
    Route::prefix('leases/{lease}')->name('leases.')->group(function () {
        Route::get('/renew', [LeaseController::class, 'renewForm'])->name('renew');
        Route::post('/renew', [LeaseController::class, 'renew']);
        Route::get('/terminate', [LeaseController::class, 'terminateForm'])->name('terminate');
        Route::post('/terminate', [LeaseController::class, 'terminate']);
        Route::get('/bills', [LeaseController::class, 'bills'])->name('bills');
        Route::get('/payment-history', [LeaseController::class, 'paymentHistory'])->name('payment-history');
        Route::get('/contract', [LeaseController::class, 'contract'])->name('contract');
        Route::get('/print', [LeaseController::class, 'print'])->name('print');
        Route::get('/deposit', [LeaseController::class, 'deposit'])->name('deposit');
    });
    
        // ========================================================================
    // BILLS - COMPLETE ROUTES (FIXED - NO DUPLICATE NAMES)
    // ========================================================================
    
    // Bills Resource (CRUD) - This automatically creates routes including:
    // bills.void if you have a void() method in your controller
    Route::resource('bills', BillController::class);
    
    // Bill collection routes (prefix routes that DON'T conflict with resource routes)
    Route::prefix('bills')->name('bills.')->group(function () {
        // Status-based filters
        Route::get('/pending', [BillController::class, 'pending'])->name('pending');
        Route::get('/overdue', [BillController::class, 'overdue'])->name('overdue');
        Route::get('/paid', [BillController::class, 'paid'])->name('paid');
        Route::get('/void/list', [BillController::class, 'voidList'])->name('void.list'); // Renamed from 'void' to 'void.list'
        
        // Bill generation
        Route::get('/generate', [BillController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [BillController::class, 'generateMonthly'])->name('generate.monthly');
        
        // Export and reports
        Route::get('/export', [BillController::class, 'export'])->name('export');
        Route::get('/statements', [BillController::class, 'statements'])->name('statements');
        Route::get('/statistics', [BillController::class, 'statistics'])->name('statistics');
        
        // Restore soft-deleted bill
        Route::post('/{id}/restore', [BillController::class, 'restore'])->name('restore')->withTrashed();
    });
    
    // Single bill routes
    Route::prefix('bills/{bill}')->name('bills.')->group(function () {
        // Document generation
        Route::get('/print', [BillController::class, 'print'])->name('print');
        Route::get('/pdf', [BillController::class, 'generatePdf'])->name('pdf');
        
        // Email
        Route::get('/send', [BillController::class, 'sendForm'])->name('send');
        Route::post('/send', [BillController::class, 'sendEmail'])->name('send.email');
        
        // Payments
        Route::get('/pay', [BillController::class, 'payForm'])->name('pay');
        Route::post('/pay', [BillController::class, 'storePayment'])->name('payments.store');
        Route::get('/payments/create', [BillController::class, 'createPayment'])->name('payments.create');
        
        // Actions - NOTE: void route is removed because it's already created by Route::resource
        // If your controller has a void() method, Resource will create bills.void automatically
        // If you need a custom void endpoint with different HTTP method, use a different name
        
        // Late fee and reminders
        Route::post('/apply-late-fee', [BillController::class, 'applyLateFee'])->name('apply-late-fee');
        Route::post('/send-reminder', [BillController::class, 'sendReminder'])->name('send-reminder');
        
        // Related data
        Route::get('/items', [BillController::class, 'items'])->name('items');
        Route::get('/payments', [BillController::class, 'payments'])->name('payments');
        Route::get('/history', [BillController::class, 'history'])->name('history');
    });
    
    // ========================================================================
    // PHASE 4: SPECIALIZED RESOURCES
    // ========================================================================
    
    // Electricity Readings
    Route::resource('electricity-readings', ElectricityReadingController::class)->except(['index', 'create']);
    Route::prefix('electricity-readings')->name('electricity-readings.')->group(function () {
        Route::get('/', [ElectricityReadingController::class, 'index'])->name('index');
        Route::get('/create/{meterReading}', [ElectricityReadingController::class, 'create'])->name('create');
        Route::get('/analysis', [ElectricityReadingController::class, 'analysis'])->name('analysis');
        Route::get('/peak-demand', [ElectricityReadingController::class, 'peakDemand'])->name('peak-demand');
        Route::get('/export', [ElectricityReadingController::class, 'export'])->name('export');
    });
    
    // Water Readings
    Route::resource('water-readings', WaterReadingController::class)->except(['index', 'create']);
    Route::prefix('water-readings')->name('water-readings.')->group(function () {
        Route::get('/', [WaterReadingController::class, 'index'])->name('index');
        Route::get('/create/{meterReading}', [WaterReadingController::class, 'create'])->name('create');
        Route::get('/leak-detection', [WaterReadingController::class, 'leakDetection'])->name('leak-detection');
        Route::get('/consumption-analysis', [WaterReadingController::class, 'consumptionAnalysis'])->name('consumption-analysis');
        Route::get('/export', [WaterReadingController::class, 'export'])->name('export');
    });
    
    // Gas Readings
    Route::resource('gas-readings', GasReadingController::class)->except(['index', 'create']);
    Route::prefix('gas-readings')->name('gas-readings.')->group(function () {
        Route::get('/', [GasReadingController::class, 'index'])->name('index');
        Route::get('/create/{meterReading}', [GasReadingController::class, 'create'])->name('create');
        Route::get('/safety-check', [GasReadingController::class, 'safetyCheck'])->name('safety-check');
        Route::get('/appliance-usage', [GasReadingController::class, 'applianceUsage'])->name('appliance-usage');
        Route::get('/export', [GasReadingController::class, 'export'])->name('export');
    });
    
    // Consumptions
    Route::resource('consumptions', ConsumptionController::class)->except(['create', 'store']);
    Route::prefix('consumptions')->name('consumptions.')->group(function () {
        Route::get('/analysis', [ConsumptionController::class, 'analysis'])->name('analysis');
        Route::get('/comparison', [ConsumptionController::class, 'comparison'])->name('comparison');
        Route::get('/trends', [ConsumptionController::class, 'trends'])->name('trends');
        Route::get('/reports', [ConsumptionController::class, 'reports'])->name('reports');
        Route::get('/export', [ConsumptionController::class, 'export'])->name('export');
        Route::get('/generate', [ConsumptionController::class, 'generateForm'])->name('generate');
        Route::post('/generate', [ConsumptionController::class, 'generate']);
    });
    
    // Bill Items
    Route::resource('bill-items', BillItemController::class)->except(['index', 'create', 'store', 'edit', 'update']);
    Route::prefix('bill-items')->name('bill-items.')->group(function () {
        Route::get('/', [BillItemController::class, 'index'])->name('index');
        Route::get('/create/{bill}', [BillItemController::class, 'create'])->name('create');
        Route::post('/store/{bill}', [BillItemController::class, 'store'])->name('store');
        Route::get('/{billItem}/edit', [BillItemController::class, 'edit'])->name('edit');
        Route::put('/{billItem}/update', [BillItemController::class, 'update'])->name('update');
        Route::get('/statistics', [BillItemController::class, 'statistics'])->name('statistics');
        Route::get('/average-rates', [BillItemController::class, 'averageRates'])->name('average-rates');
        Route::post('/bulk-adjust', [BillItemController::class, 'bulkAdjust'])->name('bulk-adjust');
        Route::post('/add-credit', [BillItemController::class, 'addCredit'])->name('add-credit');
        Route::post('/add-fee', [BillItemController::class, 'addFee'])->name('add-fee');
    });
    
    // Preventive Maintenances
    Route::resource('preventive-maintenances', PreventiveMaintenanceController::class);
    Route::prefix('preventive-maintenances')->name('preventive-maintenances.')->group(function () {
        Route::get('/due', [PreventiveMaintenanceController::class, 'due'])->name('due');
        Route::get('/overdue', [PreventiveMaintenanceController::class, 'overdue'])->name('overdue');
        Route::get('/completed', [PreventiveMaintenanceController::class, 'completed'])->name('completed');
        Route::get('/calendar', [PreventiveMaintenanceController::class, 'calendar'])->name('calendar');
        Route::get('/export', [PreventiveMaintenanceController::class, 'export'])->name('export');
    });
    Route::prefix('preventive-maintenances/{preventiveMaintenance}')->name('preventive-maintenances.')->group(function () {
        Route::get('/perform', [PreventiveMaintenanceController::class, 'performForm'])->name('perform');
        Route::post('/perform', [PreventiveMaintenanceController::class, 'perform']);
        Route::get('/schedule', [PreventiveMaintenanceController::class, 'scheduleForm'])->name('schedule');
        Route::post('/schedule', [PreventiveMaintenanceController::class, 'schedule']);
        Route::get('/history', [PreventiveMaintenanceController::class, 'history'])->name('history');
        Route::get('/checklist', [PreventiveMaintenanceController::class, 'checklist'])->name('checklist');
    });
    
    // Repairs
    Route::resource('repairs', RepairController::class);
    Route::prefix('repairs/{repair}')->name('repairs.')->group(function () {
        Route::get('/complete', [RepairController::class, 'completeForm'])->name('complete');
        Route::post('/complete', [RepairController::class, 'complete']);
        Route::get('/warranty', [RepairController::class, 'warranty'])->name('warranty');
        Route::post('/warranty', [RepairController::class, 'updateWarranty']);
        Route::get('/photos', [RepairController::class, 'photos'])->name('photos');
        Route::post('/photos', [RepairController::class, 'uploadPhotos']);
        Route::get('/parts', [RepairController::class, 'parts'])->name('parts');
        Route::post('/parts', [RepairController::class, 'updateParts']);
    });
    
    // ========================================================================
    // PHASE 5: SUPPORT RESOURCES
    // ========================================================================
    
    // Alert Rules
    Route::resource('alert-rules', AlertRuleController::class);
    Route::prefix('alert-rules/{alertRule}')->name('alert-rules.')->group(function () {
        Route::post('/activate', [AlertRuleController::class, 'activate'])->name('activate');
        Route::post('/deactivate', [AlertRuleController::class, 'deactivate'])->name('deactivate');
        Route::get('/alerts', [AlertRuleController::class, 'alerts'])->name('alerts');
        Route::get('/test', [AlertRuleController::class, 'test'])->name('test');
        Route::post('/test', [AlertRuleController::class, 'runTest']);
    });
    
    // Alerts
    Route::resource('alerts', AlertController::class);
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/unread', [AlertController::class, 'unread'])->name('unread');
        Route::get('/pending', [AlertController::class, 'pending'])->name('pending');
        Route::get('/resolved', [AlertController::class, 'resolved'])->name('resolved');
        Route::post('/mark-all-read', [AlertController::class, 'markAllRead'])->name('mark-all-read');
        Route::post('/bulk-resolve', [AlertController::class, 'bulkResolve'])->name('bulk-resolve');
        Route::get('/export', [AlertController::class, 'export'])->name('export');
    });
    Route::prefix('alerts/{alert}')->name('alerts.')->group(function () {
        Route::post('/read', [AlertController::class, 'markAsRead'])->name('read');
        Route::post('/acknowledge', [AlertController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/resolve', [AlertController::class, 'resolve'])->name('resolve');
        Route::get('/details', [AlertController::class, 'details'])->name('details');
    });
    
    // Rate Schedules
    Route::resource('rate-schedules', RateScheduleController::class);
    Route::prefix('rate-schedules/{rateSchedule}')->name('rate-schedules.')->group(function () {
        Route::get('/rates', [RateScheduleController::class, 'rates'])->name('rates');
        Route::post('/activate', [RateScheduleController::class, 'activate'])->name('activate');
        Route::post('/deactivate', [RateScheduleController::class, 'deactivate'])->name('deactivate');
        Route::get('/copy', [RateScheduleController::class, 'copy'])->name('copy');
        Route::post('/copy', [RateScheduleController::class, 'duplicate']);
    });
    
    // RATES
    Route::get('rate-schedules/{rateSchedule}/rates', [RateController::class, 'index'])->name('rate-schedules.rates.index');
    Route::get('rate-schedules/{rateSchedule}/rates/create', [RateController::class, 'create'])->name('rate-schedules.rates.create');
    Route::post('rate-schedules/{rateSchedule}/rates', [RateController::class, 'store'])->name('rate-schedules.rates.store');
    Route::get('rates/{rate}', [RateController::class, 'show'])->name('rates.show');
    Route::get('rates/{rate}/edit', [RateController::class, 'edit'])->name('rates.edit');
    Route::put('rates/{rate}', [RateController::class, 'update'])->name('rates.update');
    Route::delete('rates/{rate}', [RateController::class, 'destroy'])->name('rates.destroy');
    
    // Tax Jurisdictions
    Route::resource('tax-jurisdictions', TaxJurisdictionController::class);
    Route::prefix('tax-jurisdictions/{taxJurisdiction}')->name('tax-jurisdictions.')->group(function () {
        Route::get('/taxes', [TaxJurisdictionController::class, 'taxes'])->name('taxes');
        Route::post('/activate', [TaxJurisdictionController::class, 'activate'])->name('activate');
        Route::post('/deactivate', [TaxJurisdictionController::class, 'deactivate'])->name('deactivate');
    });
    
    // Taxes
    Route::resource('taxes', TaxController::class)->except(['index', 'create']);
    Route::prefix('taxes')->name('taxes.')->group(function () {
        Route::get('/', [TaxController::class, 'index'])->name('index');
        Route::get('/create/{type}/{id}', [TaxController::class, 'create'])->name('create');
        Route::get('/report', [TaxController::class, 'report'])->name('report');
        Route::get('/export', [TaxController::class, 'export'])->name('export');
    });
    
    // Reports
    Route::resource('reports', ReportController::class);
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/types', [ReportController::class, 'types'])->name('types');
        Route::get('/generate/{type}', [ReportController::class, 'generateForm'])->name('generate');
        Route::post('/generate/{type}', [ReportController::class, 'generate']);
        Route::get('/scheduled', [ReportController::class, 'scheduled'])->name('scheduled');
        Route::get('/templates', [ReportController::class, 'templates'])->name('templates');
        Route::get('/download/{report}', [ReportController::class, 'download'])->name('download');
        Route::post('/schedule', [ReportController::class, 'schedule'])->name('schedule');
        Route::delete('/schedule/{scheduledReport}', [ReportController::class, 'deleteSchedule'])->name('delete-schedule');
    });
    
    // Roles & Permissions
    Route::resource('roles', RoleController::class);
    Route::prefix('roles/{role}')->name('roles.')->group(function () {
        Route::get('/permissions', [RoleController::class, 'permissions'])->name('permissions');
        Route::post('/permissions', [RoleController::class, 'updatePermissions']);
        Route::get('/users', [RoleController::class, 'users'])->name('users');
        Route::post('/assign-user', [RoleController::class, 'assignUser'])->name('assign-user');
        Route::delete('/remove-user/{user}', [RoleController::class, 'removeUser'])->name('remove-user');
    });
    
    Route::resource('permissions', PermissionController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    
    // Activity Logs
    Route::resource('activity-logs', ActivityLogController::class)->only(['index', 'show']);
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/user/{user}', [ActivityLogController::class, 'userActivity'])->name('user');
        Route::get('/model/{model}/{id}', [ActivityLogController::class, 'modelActivity'])->name('model');
        Route::get('/recent', [ActivityLogController::class, 'recent'])->name('recent');
        Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
        Route::post('/clear', [ActivityLogController::class, 'clear'])->name('clear');
    });
    
    // ========================================================================
    // API & AJAX ENDPOINTS (For dynamic content)
    // ========================================================================
    
    Route::prefix('api')->name('api.')->group(function () {
        // Building stats
        Route::get('/building/{id}/units-count', [BuildingController::class, 'unitsCount'])->name('building.units-count');
        Route::get('/building/{id}/occupancy-rate', [BuildingController::class, 'occupancyRate'])->name('building.occupancy-rate');
        Route::get('/building/{id}/revenue', [BuildingController::class, 'revenue'])->name('building.revenue');
        Route::get('/building/{id}/tenants-count', [BuildingController::class, 'tenantsCount'])->name('building.tenants-count');
        
        // Tenant data
        Route::get('/tenant/{id}/balance', [TenantController::class, 'balance'])->name('tenant.balance');
        Route::get('/tenant/{id}/payment-history', [TenantController::class, 'paymentHistory'])->name('tenant.payment-history');
        Route::get('/tenant/{id}/lease-info', [TenantController::class, 'leaseInfo'])->name('tenant.lease-info');
        
        // Unit data
        Route::get('/unit/{id}/status', [UnitController::class, 'status'])->name('unit.status');
        Route::get('/unit/{id}/consumption', [UnitController::class, 'consumption'])->name('unit.consumption');
        Route::get('/unit/{id}/maintenance-history', [UnitController::class, 'maintenanceHistory'])->name('unit.maintenance-history');
        Route::get('/unit/{id}/statistics', [UnitController::class, 'statistics'])->name('unit.statistics');
        Route::post('/unit/{id}/update-status', [UnitController::class, 'updateStatus'])->name('unit.update-status');
        Route::get('/building/{id}/units', [UnitController::class, 'buildingUnits'])->name('building.units');
        
        // Unit tenants (for dropdowns)
        Route::get('/unit/{unit}/tenants', [UnitController::class, 'getTenants'])->name('unit.tenants');
        
        // Building Photos
        Route::get('/building/{id}/photos', [BuildingPhotoController::class, 'ajaxIndex'])->name('building.photos');
        Route::post('/building/{id}/photos/upload', [BuildingPhotoController::class, 'ajaxUpload'])->name('building.photos.upload');
        Route::post('/building/{id}/photos/{photo}/set-primary', [BuildingPhotoController::class, 'ajaxSetPrimary'])->name('building.photos.set-primary');
        Route::delete('/building/{id}/photos/{photo}', [BuildingPhotoController::class, 'ajaxDestroy'])->name('building.photos.destroy');
        
        // Bills API endpoints
        Route::get('/bill/{id}/status', [BillController::class, 'status'])->name('bill.status');
        Route::get('/bill/{id}/payment-details', [BillController::class, 'paymentDetails'])->name('bill.payment-details');
        Route::post('/bill/{id}/update-status', [BillController::class, 'updateStatus'])->name('bill.update-status');
        Route::get('/bills/statistics', [BillController::class, 'statistics'])->name('bills.statistics');
        Route::get('/bills/overdue', [BillController::class, 'overdue'])->name('bills.overdue');
        
        // Bill Items API endpoints
        Route::get('/bill-items/statistics', [BillItemController::class, 'statistics'])->name('bill-items.statistics');
        Route::get('/bill-items/average-rates', [BillItemController::class, 'averageRates'])->name('bill-items.average-rates');
        Route::post('/bill-items/bulk-adjust', [BillItemController::class, 'bulkAdjust'])->name('bill-items.bulk-adjust');
        Route::post('/bill-items/add-credit', [BillItemController::class, 'addCredit'])->name('bill-items.add-credit');
        Route::post('/bill-items/add-fee', [BillItemController::class, 'addFee'])->name('bill-items.add-fee');
        
        // Maintenance Requests - API endpoints
        Route::post('/maintenance-request/{id}/update-status', [MaintenanceRequestController::class, 'updateStatus'])->name('maintenance-request.update-status');
        Route::get('/maintenance-request/{id}/timeline', [MaintenanceRequestController::class, 'timeline'])->name('maintenance-request.timeline');
        Route::post('/maintenance-request/{id}/add-note', [MaintenanceRequestController::class, 'addNote'])->name('maintenance-request.add-note');
        
        // Meter Readings
        Route::get('/meter-reading/{id}/validation', [MeterReadingController::class, 'validation'])->name('meter-reading.validation');
        Route::post('/meter-reading/bulk-validate', [MeterReadingController::class, 'bulkValidate'])->name('meter-reading.bulk-validate');
        
        // Consumptions
        Route::get('/consumption/analysis/{unit}/{period}', [ConsumptionController::class, 'analysisData'])->name('consumption.analysis-data');
        Route::get('/consumption/comparison/{unit1}/{unit2}/{period}', [ConsumptionController::class, 'comparisonData'])->name('consumption.comparison-data');
        
        // Alerts
        Route::get('/alerts/count', [AlertController::class, 'count'])->name('alerts.count');
        Route::post('/alerts/bulk-action', [AlertController::class, 'bulkAction'])->name('alerts.bulk-action');
        
        // Reports
        Route::get('/report/{id}/progress', [ReportController::class, 'progress'])->name('report.progress');
        Route::post('/report/{id}/cancel', [ReportController::class, 'cancel'])->name('report.cancel');
        
        // General
        Route::get('/search/quick', [PageController::class, 'quickSearch'])->name('search.quick');
        Route::post('/upload/file', [PageController::class, 'uploadFile'])->name('upload.file');
        Route::get('/notifications/count', [PageController::class, 'notificationsCount'])->name('notifications.count');
    });
    
    // ========================================================================
    // SPECIAL PAGES & UTILITIES
    // ========================================================================
    
    // Search
    Route::get('/search', [PageController::class, 'search'])->name('search');
    Route::get('/search/autocomplete', [PageController::class, 'searchAutocomplete'])->name('search.autocomplete');
    
    // Notifications
    Route::get('/notifications', [PageController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/mark-read/{id}', [PageController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [PageController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    
    // Calendar
    Route::get('/calendar', [PageController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/events', [PageController::class, 'calendarEvents'])->name('calendar.events');
    
    // Map View
    Route::get('/map', [PageController::class, 'map'])->name('map');
    Route::get('/map/buildings', [PageController::class, 'mapBuildings'])->name('map.buildings');
    
    // Import/Export
    Route::get('/import-export', [PageController::class, 'importExport'])->name('import-export');
    Route::post('/import/{model}', [PageController::class, 'import'])->name('import');
    Route::get('/export/{model}', [PageController::class, 'export'])->name('export');
    Route::get('/templates/{model}', [PageController::class, 'downloadTemplate'])->name('templates.download');
    
    // Backup
    Route::get('/backup', [PageController::class, 'backup'])->name('backup');
    Route::post('/backup/create', [PageController::class, 'createBackup'])->name('backup.create');
    Route::post('/backup/restore', [PageController::class, 'restoreBackup'])->name('backup.restore');
    Route::delete('/backup/delete/{filename}', [PageController::class, 'deleteBackup'])->name('backup.delete');
    
    // Help & Documentation
    Route::get('/help', [PageController::class, 'help'])->name('help');
    Route::get('/documentation', [PageController::class, 'documentation'])->name('documentation');
    Route::get('/faq', [PageController::class, 'faq'])->name('faq');
    Route::get('/support', [PageController::class, 'support'])->name('support');
});

// ============================================================================
// CATCH-ALL ROUTE (Must be last)
// ============================================================================

Route::fallback(function () {
    return view('errors.404');
});