<?php

namespace App\Http\Controllers\Api\V1\FriendlyMatch;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\FriendlyMatch\StorePerformanceHighlightRequest;
use App\Http\Resources\FriendlyMatch\PerformanceHighlightResource;
use App\Models\FriendlyMatch;
use App\Models\PlayerMembership;
use App\Services\FriendlyMatch\MatchResultService;
use DomainException;
use Illuminate\Http\JsonResponse;

class PerformanceHighlightController extends BaseController
{
    public function __construct(
        private readonly MatchResultService $matchResultService,
    ) {}

    public function index(FriendlyMatch $match): JsonResponse
    {
        $this->authorize('view', $match);

        $highlights = $match->highlights()
            ->with(['playerMembership.player.user', 'playerMembership.position'])
            ->get();

        return $this->sendResponse(
            PerformanceHighlightResource::collection($highlights),
            'Destaques recuperados.'
        );
    }

    public function store(StorePerformanceHighlightRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageHighlights', $match);

        $allowedIds = $this->resolveAllowedMembershipIds($match, $request->user()->id);
        $highlights = [];

        foreach ($request->validated()['highlights'] as $item) {
            if (! in_array($item['player_membership_id'], $allowedIds, true)) {
                return $this->sendError(
                    "player_membership_id {$item['player_membership_id']} não pertence ao seu time.",
                    [],
                    403,
                );
            }

            try {
                $highlights[] = $this->matchResultService->registerHighlight($match, $item);
            } catch (DomainException $exception) {
                return $this->sendError($exception->getMessage(), [], 409);
            }
        }

        return $this->sendResponse(
            PerformanceHighlightResource::collection(collect($highlights)),
            'Estatísticas registradas.'
        );
    }

    private function resolveAllowedMembershipIds(FriendlyMatch $match, int $userId): array
    {
        return PlayerMembership::query()
            ->whereIn('team_sport_mode_id', [$match->home_team_id, $match->away_team_id])
            ->whereHas('teamSportMode.team', fn ($query) => $query->where('owner_id', $userId))
            ->pluck('id')
            ->all();
    }
}
