<?php

use App\Http\Controllers\Api\V1\System\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('health', HealthCheckController::class)->name('health');

    /*
    |--------------------------------------------------------------------------
    | Protected API routes
    |--------------------------------------------------------------------------
    |
    | After Sanctum is installed and configured, register authenticated API
    | endpoints in a dedicated auth:sanctum group here.
    |
    */
});
