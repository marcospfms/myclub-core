<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Models\Team;
use App\Models\TeamSportMode;
use Illuminate\Http\JsonResponse;
use DomainException;
use App\Services\Team\TeamService;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Team\TeamSportModeResource;
use App\Http\Requests\Team\StoreTeamSportModeRequest;

class TeamSportModeController extends BaseController
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    public function store(StoreTeamSportModeRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $teamSportMode = $this->teamService->addSportMode($team, $request->validated('sport_mode_id'));

        return $this->sendResponse(
            new TeamSportModeResource($teamSportMode->load(['sportMode', 'activeMemberships'])),
            'Modalidade adicionada ao time.',
            201
        );
    }

    public function destroy(Team $team, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('update', $team);

        if ($teamSportMode->team_id !== $team->id) {
            return $this->sendError('Modalidade do time não encontrada.', [], 404);
        }

        try {
            $this->teamService->removeSportMode($teamSportMode);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse([], 'Modalidade removida do time.');
    }
}
