<?php

namespace App\Http\Controllers\Api\V1\FriendlyMatch;

use App\Enums\ResultStatus;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\FriendlyMatch\RegisterMatchResultRequest;
use App\Http\Resources\FriendlyMatch\FriendlyMatchResource;
use App\Models\FriendlyMatch;
use App\Services\FriendlyMatch\MatchResultService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchResultController extends BaseController
{
    public function __construct(
        private readonly MatchResultService $matchResultService,
    ) {}

    public function store(RegisterMatchResultRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_status === ResultStatus::Pending) {
            return $this->sendError('Resultado já registrado e aguardando confirmação.', [], 409);
        }

        try {
            $updatedMatch = $this->matchResultService->register($match, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Resultado registrado.'
        );
    }

    public function confirm(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_registered_by === $request->user()->id) {
            return $this->sendError('O registrador não pode confirmar o próprio resultado.', [], 403);
        }

        try {
            $updatedMatch = $this->matchResultService->confirmResult($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Resultado confirmado. Amistoso encerrado.'
        );
    }

    public function dispute(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_registered_by === $request->user()->id) {
            return $this->sendError('O registrador não pode contestar o próprio resultado.', [], 403);
        }

        try {
            $updatedMatch = $this->matchResultService->disputeResult($match);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new FriendlyMatchResource($updatedMatch),
            'Resultado contestado. Registre novamente.'
        );
    }
}
