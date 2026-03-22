<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API endpoints (if needed)
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// AJAX endpoints for your web application
Route::middleware(['auth', 'web'])->prefix('ajax')->group(function () {
    Route::get('/dashboard-stats', [DashboardController::class, 'stats']);
    Route::get('/search-autocomplete', [PageController::class, 'searchAutocomplete']);
    Route::get('/building/{id}/units-count', [BuildingController::class, 'unitsCount']);
    Route::get('/tenant/{id}/balance', [TenantController::class, 'balance']);
    Route::get('/bill/{id}/status', [BillController::class, 'status']);
    Route::post('/maintenance-request/{id}/update-status', [MaintenanceRequestController::class, 'updateStatus']);
});