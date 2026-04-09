<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\SportModeResource;
use App\Services\Catalog\SportModeService;
use Illuminate\Http\JsonResponse;

class SportModeController extends BaseController
{
    public function __construct(
        private readonly SportModeService $sportModeService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            SportModeResource::collection($this->sportModeService->listAll()),
            'Sport modes retrieved.'
        );
    }
}
