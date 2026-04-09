<?php

namespace Tests\Feature\Phase1;

use App\Models\Team;
use App\Models\User;
use App\Models\Player;
use App\Models\Position;
use App\Models\SportMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamInvitationAndRosterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_send_invitation_and_invited_user_can_accept_it(): void
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();
        $sportMode = SportMode::query()->firstOrFail();
        $position = Position::query()->firstOrFail();

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Convite FC',
        ]);

        $teamSportMode = $team->sportModes()->create(['sport_mode_id' => $sportMode->id]);

        Sanctum::actingAs($owner);

        $storeResponse = $this->postJson(route('api.v1.teams.sport-modes.invitations.store', [$team, $teamSportMode]), [
            'invited_user_id' => $invitedUser->id,
            'position_id' => $position->id,
            'message' => 'Vem pro elenco',
        ]);

        $invitationId = $storeResponse->json('data.id');

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.invited_user.id', $invitedUser->id)
            ->assertJsonPath('data.position.id', $position->id)
            ->assertJsonPath('data.status', 'pending');

        Sanctum::actingAs($invitedUser);

        $this->getJson(route('api.v1.invitations.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $invitationId);

        $acceptResponse = $this->postJson(route('api.v1.invitations.accept', $invitationId));

        $acceptResponse
            ->assertOk()
            ->assertJsonPath('data.player.user_id', $invitedUser->id)
            ->assertJsonPath('data.position.id', $position->id)
            ->assertJsonPath('message', 'Convite aceito.');

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitationId,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('player_memberships', [
            'team_sport_mode_id' => $teamSportMode->id,
            'player_id' => $invitedUser->id,
            'position_id' => $position->id,
            'left_at' => null,
        ]);
    }

    public function test_public_roster_is_visible_and_player_can_leave_team(): void
    {
        $owner = User::factory()->create();
        $playerUser = User::factory()->create();
        $sportMode = SportMode::query()->firstOrFail();
        $position = Position::query()->firstOrFail();

        Player::create([
            'user_id' => $playerUser->id,
            'city' => 'Manaus',
        ]);

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Elenco FC',
        ]);

        $teamSportMode = $team->sportModes()->create(['sport_mode_id' => $sportMode->id]);

        $membership = $teamSportMode->memberships()->create([
            'player_id' => $playerUser->id,
            'position_id' => $position->id,
            'is_starter' => true,
        ]);

        $this->getJson(route('api.v1.teams.sport-modes.members.index', [$team, $teamSportMode]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $membership->id);

        Sanctum::actingAs($playerUser);

        $this->deleteJson(route('api.v1.teams.sport-modes.members.leave', [$team, $teamSportMode, $membership]))
            ->assertOk()
            ->assertJsonPath('message', 'Você saiu do elenco.');

        $this->assertDatabaseMissing('player_memberships', [
            'id' => $membership->id,
            'left_at' => null,
        ]);
    }

    public function test_owner_can_remove_member_and_conflicts_return_409(): void
    {
        $owner = User::factory()->create();
        $playerUser = User::factory()->create();
        $sportMode = SportMode::query()->firstOrFail();
        $position = Position::query()->firstOrFail();

        Player::create(['user_id' => $playerUser->id]);

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Conflito FC',
        ]);

        $teamSportMode = $team->sportModes()->create(['sport_mode_id' => $sportMode->id]);

        $membership = $teamSportMode->memberships()->create([
            'player_id' => $playerUser->id,
            'position_id' => $position->id,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.teams.sport-modes.invitations.store', [$team, $teamSportMode]), [
            'invited_user_id' => $playerUser->id,
            'position_id' => $position->id,
        ])->assertStatus(409);

        $this->deleteJson(route('api.v1.teams.sport-modes.destroy', [$team, $teamSportMode]))
            ->assertStatus(409);

        $this->deleteJson(route('api.v1.teams.sport-modes.members.destroy', [$team, $teamSportMode, $membership]))
            ->assertOk();

        $this->assertDatabaseMissing('player_memberships', [
            'id' => $membership->id,
            'left_at' => null,
        ]);
    }
}
