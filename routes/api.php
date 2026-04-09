<?php

use App\Http\Controllers\Api\Catalog\BadgeTypeController;
use App\Http\Controllers\Api\Catalog\CategoryController;
use App\Http\Controllers\Api\Catalog\FormationController;
use App\Http\Controllers\Api\Catalog\PositionController;
use App\Http\Controllers\Api\Catalog\SportModeController;
use App\Http\Controllers\Api\Catalog\StaffRoleController;
use App\Http\Controllers\Api\V1\System\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('health', HealthCheckController::class)->name('health');

    Route::middleware('auth:sanctum')->prefix('catalog')->name('catalog.')->group(function () {
        Route::get('sport-modes', [SportModeController::class, 'index'])->name('sport-modes.index');
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
        Route::get('formations', [FormationController::class, 'index'])->name('formations.index');
        Route::get('staff-roles', [StaffRoleController::class, 'index'])->name('staff-roles.index');
        Route::get('badge-types', [BadgeTypeController::class, 'index'])->name('badge-types.index');
    });
});
