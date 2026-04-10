<?php

namespace App\Http\Controllers\Api\V1\Championship;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Championship\EnrollTeamRequest;
use App\Http\Requests\Championship\SelectPlayersRequest;
use App\Http\Resources\Championship\ChampionshipTeamPlayerResource;
use App\Http\Resources\Championship\ChampionshipTeamResource;
use App\Models\Championship;
use App\Models\TeamSportMode;
use App\Services\Championship\ChampionshipEnrollmentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChampionshipEnrollmentController extends BaseController
{
    public function __construct(
        private readonly ChampionshipEnrollmentService $enrollmentService,
    ) {}

    public function index(Championship $championship): JsonResponse
    {
        $this->authorize('view', $championship);

        $teams = $championship->teams()->with(['teamSportMode.team', 'teamSportMode.sportMode'])->get();

        return $this->sendResponse(
            ChampionshipTeamResource::collection($teams),
            'Times inscritos recuperados.'
        );
    }

    public function enroll(EnrollTeamRequest $request, Championship $championship): JsonResponse
    {
        $this->authorize('enroll', $championship);

        $teamSportMode = TeamSportMode::query()
            ->with('team')
            ->findOrFail($request->integer('team_sport_mode_id'));

        if (! request()->user()->isAdmin() && $teamSportMode->team->owner_id !== request()->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        try {
            $enrolled = $this->enrollmentService->enroll($championship, $teamSportMode);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipTeamResource($enrolled->load(['teamSportMode.team', 'teamSportMode.sportMode'])),
            'Time inscrito.',
            201
        );
    }

    public function removeTeam(Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('manageEnrollment', $championship);

        try {
            $this->enrollmentService->removeTeam($championship, $teamSportMode);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse([], 'Time removido da inscrição.');
    }

    public function players(Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('view', $championship);

        $players = $championship->teams()
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->firstOrFail()
            ->players()
            ->with(['membership.player.user', 'membership.position'])
            ->get();

        return $this->sendResponse(
            ChampionshipTeamPlayerResource::collection($players),
            'Jogadores inscritos recuperados.'
        );
    }

    public function selectPlayers(SelectPlayersRequest $request, Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('enroll', $championship);

        if (! request()->user()->isAdmin() && $teamSportMode->team->owner_id !== request()->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        try {
            $this->enrollmentService->selectPlayers(
                $championship,
                $teamSportMode,
                $request->validated('player_membership_ids'),
            );
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        $players = $championship->teams()
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->firstOrFail()
            ->players()
            ->with(['membership.player.user', 'membership.position'])
            ->get();

        return $this->sendResponse(
            ChampionshipTeamPlayerResource::collection($players),
            'Jogadores selecionados.'
        );
    }
}
