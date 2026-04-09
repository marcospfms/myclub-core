<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Catalog\StaffRoleResource;
use App\Services\Catalog\StaffRoleService;
use Illuminate\Http\JsonResponse;

class StaffRoleController extends BaseController
{
    public function __construct(
        private readonly StaffRoleService $staffRoleService,
    ) {}

    public function index(): JsonResponse
    {
        return $this->sendResponse(
            StaffRoleResource::collection($this->staffRoleService->listAll()),
            'Staff roles retrieved.'
        );
    }
}
