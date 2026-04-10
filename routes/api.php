<?php

use App\Http\Controllers\Api\Catalog\BadgeTypeController;
use App\Http\Controllers\Api\Catalog\CategoryController;
use App\Http\Controllers\Api\Catalog\FormationController;
use App\Http\Controllers\Api\Catalog\PositionController;
use App\Http\Controllers\Api\Catalog\SportModeController;
use App\Http\Controllers\Api\Catalog\StaffRoleController;
use App\Http\Controllers\Api\V1\Championship\ChampionshipController;
use App\Http\Controllers\Api\V1\Championship\ChampionshipEnrollmentController;
use App\Http\Controllers\Api\V1\Championship\ChampionshipMatchController;
use App\Http\Controllers\Api\V1\Championship\ChampionshipMatchHighlightController;
use App\Http\Controllers\Api\V1\FriendlyMatch\FriendlyMatchController;
use App\Http\Controllers\Api\V1\FriendlyMatch\MatchResultController;
use App\Http\Controllers\Api\V1\FriendlyMatch\PerformanceHighlightController;
use App\Http\Controllers\Api\V1\Player\PlayerController;
use App\Http\Controllers\Api\V1\System\HealthCheckController;
use App\Http\Controllers\Api\V1\Team\TeamController;
use App\Http\Controllers\Api\V1\Team\TeamInvitationController;
use App\Http\Controllers\Api\V1\Team\TeamRosterController;
use App\Http\Controllers\Api\V1\Team\TeamSportModeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('health', HealthCheckController::class)->name('health');
    Route::get('teams/{team}', [TeamController::class, 'show'])->name('teams.show');
    Route::get('teams/{team}/sport-modes/{teamSportMode}/members', [TeamRosterController::class, 'index'])->name('teams.sport-modes.members.index');
    Route::get('friendly-matches/{match}', [FriendlyMatchController::class, 'show'])->name('friendly-matches.show');
    Route::get('friendly-matches/{match}/highlights', [PerformanceHighlightController::class, 'index'])->name('friendly-matches.highlights.index');

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
        Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
        Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        Route::prefix('teams/{team}/sport-modes')->name('teams.sport-modes.')->group(function () {
            Route::post('/', [TeamSportModeController::class, 'store'])->name('store');
            Route::delete('/{teamSportMode}', [TeamSportModeController::class, 'destroy'])->name('destroy');

            Route::delete('/{teamSportMode}/members/{membership}', [TeamRosterController::class, 'destroy'])->name('members.destroy');
            Route::delete('/{teamSportMode}/members/{membership}/leave', [TeamRosterController::class, 'leave'])->name('members.leave');

            Route::post('/{teamSportMode}/invitations', [TeamInvitationController::class, 'store'])->name('invitations.store');
        });

        Route::get('invitations', [TeamInvitationController::class, 'index'])->name('invitations.index');
        Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('invitations/{invitation}/reject', [TeamInvitationController::class, 'reject'])->name('invitations.reject');

        Route::prefix('friendly-matches')->name('friendly-matches.')->group(function () {
            Route::get('/', [FriendlyMatchController::class, 'index'])->name('index');
            Route::post('/', [FriendlyMatchController::class, 'store'])->name('store');
            Route::delete('/{match}', [FriendlyMatchController::class, 'destroy'])->name('destroy');
            Route::post('/{match}/confirm', [FriendlyMatchController::class, 'confirm'])->name('confirm');
            Route::post('/{match}/reject', [FriendlyMatchController::class, 'reject'])->name('reject');
            Route::post('/{match}/cancel', [FriendlyMatchController::class, 'cancel'])->name('cancel');
            Route::post('/{match}/postpone', [FriendlyMatchController::class, 'postpone'])->name('postpone');

            Route::post('/{match}/result', [MatchResultController::class, 'store'])->name('result.store');
            Route::post('/{match}/result/confirm', [MatchResultController::class, 'confirm'])->name('result.confirm');
            Route::post('/{match}/result/dispute', [MatchResultController::class, 'dispute'])->name('result.dispute');

            Route::post('/{match}/highlights', [PerformanceHighlightController::class, 'store'])->name('highlights.store');
        });

        Route::prefix('championships')->name('championships.')->group(function () {
            Route::get('/', [ChampionshipController::class, 'index'])->name('index');
            Route::post('/', [ChampionshipController::class, 'store'])->name('store');
            Route::get('/{championship}', [ChampionshipController::class, 'show'])->name('show');
            Route::put('/{championship}', [ChampionshipController::class, 'update'])->name('update');
            Route::delete('/{championship}', [ChampionshipController::class, 'destroy'])->name('destroy');
            Route::post('/{championship}/open-enrollment', [ChampionshipController::class, 'openEnrollment'])->name('open-enrollment');
            Route::post('/{championship}/activate', [ChampionshipController::class, 'activate'])->name('activate');
            Route::post('/{championship}/cancel', [ChampionshipController::class, 'cancel'])->name('cancel');
            Route::get('/{championship}/awards', [ChampionshipController::class, 'awards'])->name('awards');

            Route::get('/{championship}/teams', [ChampionshipEnrollmentController::class, 'index'])->name('teams.index');
            Route::post('/{championship}/teams', [ChampionshipEnrollmentController::class, 'enroll'])->name('teams.enroll');
            Route::delete('/{championship}/teams/{teamSportMode}', [ChampionshipEnrollmentController::class, 'removeTeam'])->name('teams.destroy');
            Route::get('/{championship}/teams/{teamSportMode}/players', [ChampionshipEnrollmentController::class, 'players'])->name('teams.players.index');
            Route::post('/{championship}/teams/{teamSportMode}/players', [ChampionshipEnrollmentController::class, 'selectPlayers'])->name('teams.players.store');

            Route::get('/{championship}/matches', [ChampionshipMatchController::class, 'index'])->name('matches.index');
            Route::get('/{championship}/matches/{match}', [ChampionshipMatchController::class, 'show'])->name('matches.show');
            Route::put('/{championship}/matches/{match}', [ChampionshipMatchController::class, 'update'])->name('matches.update');
            Route::post('/{championship}/matches/{match}/cancel', [ChampionshipMatchController::class, 'cancel'])->name('matches.cancel');

            Route::get('/{championship}/matches/{match}/highlights', [ChampionshipMatchHighlightController::class, 'index'])->name('matches.highlights.index');
            Route::post('/{championship}/matches/{match}/highlights', [ChampionshipMatchHighlightController::class, 'store'])->name('matches.highlights.store');
        });
    });
});
