<?php

use App\Http\Controllers\Admin\Catalog\BadgeTypeController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\FormationController;
use App\Http\Controllers\Admin\Catalog\PositionController;
use App\Http\Controllers\Admin\Catalog\SportModeController;
use App\Http\Controllers\Admin\Catalog\StaffRoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('catalog')->name('catalog.')->group(function () {
            Route::resource('sport-modes', SportModeController::class)->except(['show']);
            Route::resource('categories', CategoryController::class)->except(['show']);
            Route::resource('positions', PositionController::class)->except(['show']);
            Route::resource('formations', FormationController::class)->except(['show']);
            Route::resource('staff-roles', StaffRoleController::class)->except(['show']);
            Route::resource('badge-types', BadgeTypeController::class)->except(['show']);
        });
    });
});

require __DIR__.'/settings.php';
