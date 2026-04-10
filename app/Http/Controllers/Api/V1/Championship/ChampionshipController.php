<?php

namespace App\Http\Controllers\Api\V1\Championship;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Championship\StoreChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use App\Http\Resources\Championship\ChampionshipAwardResource;
use App\Http\Resources\Championship\ChampionshipResource;
use App\Models\Championship;
use App\Services\Championship\ChampionshipService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChampionshipController extends BaseController
{
    public function __construct(
        private readonly ChampionshipService $championshipService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $championships = Championship::query()
            ->where('created_by', $request->user()->id)
            ->with(['creator', 'category', 'sportModes', 'teams.teamSportMode.team'])
            ->latest()
            ->get();

        return $this->sendResponse(
            ChampionshipResource::collection($championships),
            'Campeonatos recuperados.'
        );
    }

    public function store(StoreChampionshipRequest $request): JsonResponse
    {
        try {
            $championship = $this->championshipService->create($request->validated(), $request->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Campeonato criado.',
            201
        );
    }

    public function show(Request $request, Championship $championship): JsonResponse
    {
        $this->authorize('view', $championship);

        return $this->sendResponse(
            new ChampionshipResource($this->championshipService->loadForApi($championship)),
            'Campeonato recuperado.'
        );
    }

    public function update(UpdateChampionshipRequest $request, Championship $championship): JsonResponse
    {
        $this->authorize('update', $championship);

        try {
            $championship = $this->championshipService->update($championship, $request->validated());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Campeonato atualizado.'
        );
    }

    public function destroy(Championship $championship): JsonResponse
    {
        $this->authorize('delete', $championship);

        try {
            $championship = $this->championshipService->cancel($championship, request()->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Campeonato cancelado.'
        );
    }

    public function openEnrollment(Championship $championship): JsonResponse
    {
        $this->authorize('manageLifecycle', $championship);

        try {
            $championship = $this->championshipService->openEnrollment($championship);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Inscrições abertas.'
        );
    }

    public function activate(Championship $championship): JsonResponse
    {
        $this->authorize('manageLifecycle', $championship);

        try {
            $championship = $this->championshipService->activate($championship);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Campeonato ativado.'
        );
    }

    public function cancel(Championship $championship): JsonResponse
    {
        if ($championship->isActive()) {
            $this->authorize('cancelActive', $championship);
        } else {
            $this->authorize('manageLifecycle', $championship);
        }

        try {
            $championship = $this->championshipService->cancel($championship, request()->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new ChampionshipResource($championship),
            'Campeonato cancelado.'
        );
    }

    public function awards(Championship $championship): JsonResponse
    {
        $this->authorize('view', $championship);

        $awards = $championship->awards()->with('player.user')->get();

        return $this->sendResponse(
            ChampionshipAwardResource::collection($awards),
            'Premiações recuperadas.'
        );
    }
}
