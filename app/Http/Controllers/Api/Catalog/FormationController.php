<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\FormationResource;
use App\Services\Catalog\FormationService;
use Illuminate\Http\JsonResponse;

class FormationController extends BaseController
{
    public function __construct(
        private readonly FormationService $formationService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            FormationResource::collection($this->formationService->listAll()),
            'Formations retrieved.'
        );
    }
}
