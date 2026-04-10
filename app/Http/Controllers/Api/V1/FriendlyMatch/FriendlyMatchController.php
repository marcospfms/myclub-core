<?php

namespace App\Http\Controllers\Api\V1\FriendlyMatch;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\FriendlyMatch\PostponeFriendlyMatchRequest;
use App\Http\Requests\FriendlyMatch\StoreFriendlyMatchRequest;
use App\Http\Resources\FriendlyMatch\FriendlyMatchResource;
use App\Models\FriendlyMatch;
use App\Services\FriendlyMatch\FriendlyMatchService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendlyMatchController extends BaseController
{
    public function __construct(
        private readonly FriendlyMatchService $friendlyMatchService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $matches = $this->friendlyMatchService->listForUser($request->user());

        return $this->sendResponse(
            FriendlyMatchResource::collection($matches),
            'Amistosos recuperados.'
        );
    }

    public function store(StoreFriendlyMatchRequest $request): JsonResponse
    {
        try {
            $match = $this->friendlyMatchService->create($request->validated(), $request->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($match),
            'Amistoso criado.',
            201
        );
    }

    public function show(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('view', $match);

        return $this->sendResponse(
            new FriendlyMatchResource($this->friendlyMatchService->loadForApi($match)),
            'Amistoso recuperado.'
        );
    }

    public function destroy(FriendlyMatch $match): JsonResponse
    {
        $this->authorize('delete', $match);

        try {
            $this->friendlyMatchService->removePendingInvite($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse([], 'Convite removido.');
    }

    public function confirm(FriendlyMatch $match): JsonResponse
    {
        $this->authorize('respond', $match);

        try {
            $updatedMatch = $this->friendlyMatchService->confirm($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Amistoso confirmado.'
        );
    }

    public function reject(FriendlyMatch $match): JsonResponse
    {
        $this->authorize('respond', $match);

        try {
            $updatedMatch = $this->friendlyMatchService->reject($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Amistoso recusado.'
        );
    }

    public function cancel(FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manage', $match);

        try {
            $updatedMatch = $this->friendlyMatchService->cancel($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Amistoso cancelado.'
        );
    }

    public function postpone(PostponeFriendlyMatchRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manage', $match);

        try {
            $updatedMatch = $this->friendlyMatchService->postpone($match, $request->validated());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Amistoso adiado.'
        );
    }
}
