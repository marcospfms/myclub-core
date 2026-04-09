<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\PositionResource;
use App\Services\Catalog\PositionService;
use Illuminate\Http\JsonResponse;

class PositionController extends BaseController
{
    public function __construct(
        private readonly PositionService $positionService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            PositionResource::collection($this->positionService->listAll()),
            'Positions retrieved.'
        );
    }
}
