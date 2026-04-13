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
    private function isProductUserRequest(Request $request): bool
    {
        return $request->user()?->isAdmin() !== true;
    }

    private function rejectInvalidAuthentication(Request $request, int $code = 422): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->sendError(
            'As credenciais informadas são inválidas.',
            [],
            $code
        );
    }

    private function loadAuthUser(Request $request)
    {
        return $request->user()?->load([
            'player.user',
            'staffMember.user',
            'staffMember.role',
            'ownedTeams.owner',
            'ownedTeams.sportModes.sportMode',
        ]);
    }

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

        if (! $this->isProductUserRequest($request)) {
            return $this->rejectInvalidAuthentication($request);
        }

        return $this->sendResponse(
            new AuthUserResource($this->loadAuthUser($request)),
            'Sessão autenticada.'
        );
    }

    public function me(Request $request): JsonResponse
    {
        if (! $this->isProductUserRequest($request)) {
            return $this->rejectInvalidAuthentication($request, 401);
        }

        return $this->sendResponse(
            new AuthUserResource($this->loadAuthUser($request)),
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
