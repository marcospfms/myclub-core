<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\BadgeTypeResource;
use App\Services\Catalog\BadgeTypeService;
use Illuminate\Http\JsonResponse;

class BadgeTypeController extends BaseController
{
    public function __construct(
        private readonly BadgeTypeService $badgeTypeService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            BadgeTypeResource::collection($this->badgeTypeService->listAll()),
            'Badge types retrieved.'
        );
    }
}
