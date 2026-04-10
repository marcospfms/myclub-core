<?php

namespace App\Http\Controllers\Api\V1\Championship;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Championship\RegisterChampionshipMatchResultRequest;
use App\Http\Resources\Championship\ChampionshipMatchResource;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Services\Championship\ChampionshipMatchService;
use DomainException;
use Illuminate\Http\JsonResponse;

class ChampionshipMatchController extends BaseController
{
    public function __construct(
        private readonly ChampionshipMatchService $matchService,
    ) {}

    public function index(Championship $championship): JsonResponse
    {
        $this->authorize('view', $championship);

        $matches = ChampionshipMatch::query()
            ->whereHas('round.phase', fn ($query) => $query->where('championship_id', $championship->id))
            ->with(['round', 'homeTeam.team', 'homeTeam.sportMode', 'awayTeam.team', 'awayTeam.sportMode'])
            ->orderBy('championship_round_id')
            ->orderBy('id')
            ->get();

        return $this->sendResponse(
            ChampionshipMatchResource::collection($matches),
            'Partidas recuperadas.'
        );
    }

    public function show(Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('view', $championship);

        if (! $this->belongsToChampionship($championship, $match)) {
            return $this->sendError('Partida não encontrada.', [], 404);
        }

        return $this->sendResponse(
            new ChampionshipMatchResource($match->load([
                'round',
                'homeTeam.team',
                'homeTeam.sportMode',
                'awayTeam.team',
                'awayTeam.sportMode',
                'highlights.playerMembership.player.user',
                'highlights.playerMembership.position',
            ])),
            'Partida recuperada.'
        );
    }

    public function update(RegisterChampionshipMatchResultRequest $request, Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);

        if (! $this->belongsToChampionship($championship, $match)) {
            return $this->sendError('Partida não encontrada.', [], 404);
        }

        try {
            $match = $this->matchService->registerResult($match, $request->validated());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipMatchResource($match->load(['round', 'homeTeam.team', 'homeTeam.sportMode', 'awayTeam.team', 'awayTeam.sportMode'])),
            'Resultado registrado.'
        );
    }

    public function cancel(Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);

        if (! $this->belongsToChampionship($championship, $match)) {
            return $this->sendError('Partida não encontrada.', [], 404);
        }

        try {
            $match = $this->matchService->cancelMatch($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipMatchResource($match->load(['round', 'homeTeam.team', 'homeTeam.sportMode', 'awayTeam.team', 'awayTeam.sportMode'])),
            'Partida cancelada.'
        );
    }

    private function belongsToChampionship(Championship $championship, ChampionshipMatch $match): bool
    {
        return $match->round()->whereHas('phase', fn ($query) => $query->where('championship_id', $championship->id))->exists();
    }
}
