<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Return a successful JSON response.
     *
     * @param  mixed  $result
     */
    public function sendResponse(mixed $result, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $message,
        ], $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param  array<string, mixed>  $errorMessages
     */
    public function sendError(string $error, array $errorMessages = [], int $code = 404): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $errorMessages !== [] ? $errorMessages : null,
            'message' => $error,
        ], $code);
    }
}
