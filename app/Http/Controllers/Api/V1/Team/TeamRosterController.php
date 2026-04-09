<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Models\TeamSportMode;
use Illuminate\Http\JsonResponse;
use App\Models\PlayerMembership;
use App\Services\Team\TeamRosterService;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Team\PlayerMembershipResource;

class TeamRosterController extends BaseController
{
    public function __construct(
        private readonly TeamRosterService $rosterService,
    ) {}

    public function index(Team $team, TeamSportMode $teamSportMode): JsonResponse
    {
        if ($teamSportMode->team_id !== $team->id) {
            return $this->sendError('Modalidade do time não encontrada.', [], 404);
        }

        $members = $this->rosterService->listActiveMembers($teamSportMode);

        return $this->sendResponse(
            PlayerMembershipResource::collection($members),
            'Roster retrieved.'
        );
    }

    public function destroy(Team $team, TeamSportMode $teamSportMode, PlayerMembership $membership): JsonResponse
    {
        $this->authorize('manageRoster', $team);

        if ($teamSportMode->team_id !== $team->id || $membership->team_sport_mode_id !== $teamSportMode->id) {
            return $this->sendError('Membro do elenco não encontrado.', [], 404);
        }

        $this->rosterService->removeMember($membership);

        return $this->sendResponse([], 'Jogador removido do elenco.');
    }

    public function leave(Request $request, Team $team, TeamSportMode $teamSportMode, PlayerMembership $membership): JsonResponse
    {
        if ($teamSportMode->team_id !== $team->id || $membership->team_sport_mode_id !== $teamSportMode->id) {
            return $this->sendError('Membro do elenco não encontrado.', [], 404);
        }

        if ($membership->player_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        $this->rosterService->leaveTeam($membership);

        return $this->sendResponse([], 'Você saiu do elenco.');
    }
}
