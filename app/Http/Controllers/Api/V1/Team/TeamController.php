<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Team\TeamService;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Team\TeamResource;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;

class TeamController extends BaseController
{
    public function __construct(
        private readonly TeamService $teamService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $teams = $this->teamService->listOwnedByUser($request->user());

        return $this->sendResponse(
            TeamResource::collection($teams),
            'Teams retrieved.'
        );
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->create($request->validated(), $request->user());

        return $this->sendResponse(
            new TeamResource($team),
            'Time criado.',
            201
        );
    }

    public function show(Team $team): JsonResponse
    {
        $team = $this->teamService->loadForApi($team);

        return $this->sendResponse(
            new TeamResource($team),
            'Team retrieved.'
        );
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $team = $this->teamService->update($team, $request->validated());

        return $this->sendResponse(
            new TeamResource($team),
            'Time atualizado.'
        );
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $this->teamService->deactivate($team);

        return $this->sendResponse([], 'Time desativado.');
    }
}
