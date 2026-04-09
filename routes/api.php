<?php

use App\Http\Controllers\Api\Catalog\BadgeTypeController;
use App\Http\Controllers\Api\Catalog\CategoryController;
use App\Http\Controllers\Api\Catalog\FormationController;
use App\Http\Controllers\Api\Catalog\PositionController;
use App\Http\Controllers\Api\Catalog\SportModeController;
use App\Http\Controllers\Api\Catalog\StaffRoleController;
use App\Http\Controllers\Api\V1\Team\TeamController;
use App\Http\Controllers\Api\V1\Team\TeamRosterController;
use App\Http\Controllers\Api\V1\Player\PlayerController;
use App\Http\Controllers\Api\V1\Team\TeamSportModeController;
use App\Http\Controllers\Api\V1\Staff\StaffMemberController;
use App\Http\Controllers\Api\V1\Team\TeamInvitationController;
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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('players', [PlayerController::class, 'store'])->name('players.store');
        Route::put('players', [PlayerController::class, 'update'])->name('players.update');
        Route::get('players/{player}', [PlayerController::class, 'show'])->name('players.show');

        Route::post('staff-members', [StaffMemberController::class, 'store'])->name('staff-members.store');
        Route::put('staff-members', [StaffMemberController::class, 'update'])->name('staff-members.update');

        Route::apiResource('teams', TeamController::class);

        Route::prefix('teams/{team}/sport-modes')->name('teams.sport-modes.')->group(function () {
            Route::post('/', [TeamSportModeController::class, 'store'])->name('store');
            Route::delete('/{teamSportMode}', [TeamSportModeController::class, 'destroy'])->name('destroy');

            Route::get('/{teamSportMode}/members', [TeamRosterController::class, 'index'])->name('members.index');
            Route::delete('/{teamSportMode}/members/{membership}', [TeamRosterController::class, 'destroy'])->name('members.destroy');
            Route::delete('/{teamSportMode}/members/{membership}/leave', [TeamRosterController::class, 'leave'])->name('members.leave');

            Route::post('/{teamSportMode}/invitations', [TeamInvitationController::class, 'store'])->name('invitations.store');
        });

        Route::get('invitations', [TeamInvitationController::class, 'index'])->name('invitations.index');
        Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('invitations/{invitation}/reject', [TeamInvitationController::class, 'reject'])->name('invitations.reject');
    });
});
