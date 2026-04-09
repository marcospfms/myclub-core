<?php

namespace App\Http\Controllers\Api\V1\Player;

use App\Models\Player;
use Illuminate\Http\JsonResponse;
use App\Services\Player\PlayerService;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Player\PlayerResource;
use App\Http\Requests\Player\StorePlayerRequest;
use App\Http\Requests\Player\UpdatePlayerRequest;

class PlayerController extends BaseController
{
    public function __construct(
        private readonly PlayerService $playerService,
    ) {}

    public function show(Player $player): JsonResponse
    {
        return $this->sendResponse(
            new PlayerResource($player->load('user')),
            'Player retrieved.'
        );
    }

    public function store(StorePlayerRequest $request): JsonResponse
    {
        if ($request->user()->player) {
            return $this->sendError('Perfil de jogador já existe.', [], 409);
        }

        $player = $this->playerService->createProfile($request->validated(), $request->user());

        return $this->sendResponse(
            new PlayerResource($player->load('user')),
            'Perfil de jogador criado.',
            201
        );
    }

    public function update(UpdatePlayerRequest $request): JsonResponse
    {
        $player = $request->user()->player;

        if (!$player) {
            return $this->sendError('Perfil de jogador não encontrado.', [], 404);
        }

        $player = $this->playerService->updateProfile($player, $request->validated());

        return $this->sendResponse(
            new PlayerResource($player->load('user')),
            'Perfil atualizado.'
        );
    }
}
