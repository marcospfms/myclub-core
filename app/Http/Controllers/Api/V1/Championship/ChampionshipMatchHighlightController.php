<?php

namespace App\Http\Controllers\Api\V1\Championship;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Championship\StoreChampionshipMatchHighlightsRequest;
use App\Http\Resources\Championship\ChampionshipMatchHighlightResource;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Services\Championship\ChampionshipMatchService;
use DomainException;
use Illuminate\Http\JsonResponse;

class ChampionshipMatchHighlightController extends BaseController
{
    public function __construct(
        private readonly ChampionshipMatchService $matchService,
    ) {}

    public function index(Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('view', $championship);

        if (! $this->belongsToChampionship($championship, $match)) {
            return $this->sendError('Partida não encontrada.', [], 404);
        }

        $highlights = $match->highlights()
            ->with(['playerMembership.player.user', 'playerMembership.position'])
            ->get();

        return $this->sendResponse(
            ChampionshipMatchHighlightResource::collection($highlights),
            'Estatísticas recuperadas.'
        );
    }

    public function store(StoreChampionshipMatchHighlightsRequest $request, Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);

        if (! $this->belongsToChampionship($championship, $match)) {
            return $this->sendError('Partida não encontrada.', [], 404);
        }

        try {
            $this->matchService->registerHighlights($match, $request->validated('highlights'));
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        $highlights = $match->highlights()
            ->with(['playerMembership.player.user', 'playerMembership.position'])
            ->get();

        return $this->sendResponse(
            ChampionshipMatchHighlightResource::collection($highlights),
            'Estatísticas registradas.'
        );
    }

    private function belongsToChampionship(Championship $championship, ChampionshipMatch $match): bool
    {
        return $match->round()->whereHas('phase', fn ($query) => $query->where('championship_id', $championship->id))->exists();
    }
}
