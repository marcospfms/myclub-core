<?php

namespace App\Http\Controllers\Api\V1\Staff;

use Illuminate\Http\JsonResponse;
use App\Services\Staff\StaffMemberService;
use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\Staff\StaffMemberResource;
use App\Http\Requests\Staff\StoreStaffMemberRequest;
use App\Http\Requests\Staff\UpdateStaffMemberRequest;

class StaffMemberController extends BaseController
{
    public function __construct(
        private readonly StaffMemberService $staffMemberService,
    ) {}

    public function store(StoreStaffMemberRequest $request): JsonResponse
    {
        if ($request->user()->staffMember) {
            return $this->sendError('Perfil de comissão técnica já existe.', [], 409);
        }

        $staffMember = $this->staffMemberService->createProfile($request->validated(), $request->user());

        return $this->sendResponse(
            new StaffMemberResource($staffMember->load(['user', 'role'])),
            'Perfil de comissão técnica criado.',
            201
        );
    }

    public function update(UpdateStaffMemberRequest $request): JsonResponse
    {
        $staffMember = $request->user()->staffMember;

        if (!$staffMember) {
            return $this->sendError('Perfil de comissão técnica não encontrado.', [], 404);
        }

        $staffMember = $this->staffMemberService->updateProfile($staffMember, $request->validated());

        return $this->sendResponse(
            new StaffMemberResource($staffMember->load(['user', 'role'])),
            'Perfil de comissão técnica atualizado.'
        );
    }
}
