<?php

namespace App\Http\Controllers\Api\V1\Team;

use App\Enums\InvitationStatus;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Models\TeamInvitation;
use App\Models\TeamSportMode;
use Illuminate\Http\JsonResponse;
use DomainException;
use App\Services\Team\TeamRosterService;
use App\Http\Controllers\Api\BaseController;
use App\Services\Team\TeamInvitationService;
use App\Http\Resources\Team\TeamInvitationResource;
use App\Http\Resources\Team\PlayerMembershipResource;
use App\Http\Requests\Team\StoreTeamInvitationRequest;

class TeamInvitationController extends BaseController
{
    public function __construct(
        private readonly TeamInvitationService $invitationService,
        private readonly TeamRosterService $rosterService,
    ) {}

    public function store(StoreTeamInvitationRequest $request, Team $team, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('manageRoster', $team);

        if ($teamSportMode->team_id !== $team->id) {
            return $this->sendError('Modalidade do time não encontrada.', [], 404);
        }

        try {
            $invitation = $this->invitationService->send($teamSportMode, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new TeamInvitationResource($invitation->load(['teamSportMode.sportMode', 'invitedUser', 'position'])),
            'Convite enviado.',
            201
        );
    }

    public function index(Request $request): JsonResponse
    {
        $invitations = $this->invitationService->listPendingForUser($request->user());

        return $this->sendResponse(
            TeamInvitationResource::collection($invitations),
            'Invitations retrieved.'
        );
    }

    public function accept(Request $request, TeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invited_user_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        if (!$invitation->isPending()) {
            return $this->sendError('Convite não está mais pendente.', [], 409);
        }

        try {
            $membership = $this->rosterService->acceptInvitation($invitation);
        } catch (DomainException $exception) {
            return $this->sendError($exception->getMessage(), [], 409);
        }

        return $this->sendResponse(
            new PlayerMembershipResource($membership->load(['player.user', 'position'])),
            'Convite aceito.'
        );
    }

    public function reject(Request $request, TeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invited_user_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        if (!$invitation->isPending()) {
            return $this->sendError('Convite não está mais pendente.', [], 409);
        }

        $this->invitationService->reject($invitation);

        return $this->sendResponse([], 'Convite recusado.');
    }
}
