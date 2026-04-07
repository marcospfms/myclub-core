<?php

namespace App\Http\Controllers\Api\V1\System;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->sendResponse([
            'status' => 'ok',
            'version' => 'v1',
            'timestamp' => now()->toIso8601String(),
            'request_id' => $request->headers->get('X-Request-Id'),
        ], 'API is healthy.');
    }
}
