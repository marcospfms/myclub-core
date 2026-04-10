<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Http\Resources\Auth\AuthUserResource;

class AuthController extends BaseController
{
    public function login(ApiLoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return $this->sendError(
                'As credenciais informadas são inválidas.',
                [],
                422
            );
        }

        $request->session()->regenerate();

        return $this->sendResponse(
            new AuthUserResource($request->user()),
            'Sessão autenticada.'
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->sendResponse(
            new AuthUserResource($request->user()),
            'Sessão autenticada.'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->sendResponse([], 'Sessão encerrada.');
    }
}
