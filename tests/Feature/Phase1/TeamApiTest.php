<?php

namespace Tests\Feature\Phase1;

use App\Models\Team;
use App\Models\User;
use App\Models\SportMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_authenticated_user_can_create_list_update_and_deactivate_owned_teams(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $sportModes = SportMode::query()->take(2)->get();

        Team::create([
            'owner_id' => $otherOwner->id,
            'name' => 'Outro Time',
            'description' => 'Não deve aparecer',
        ]);

        Sanctum::actingAs($owner);

        $storeResponse = $this->postJson(route('api.v1.teams.store'), [
            'name' => 'Atlético Manaus',
            'description' => 'Time de bairro',
            'badge' => 'teams/atletico.svg',
            'sport_mode_ids' => $sportModes->pluck('id')->all(),
        ]);

        $teamId = $storeResponse->json('data.id');

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Atlético Manaus')
            ->assertJsonCount(2, 'data.sport_modes');

        $this->getJson(route('api.v1.teams.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $teamId);

        $this->putJson(route('api.v1.teams.update', $teamId), [
            'name' => 'Atlético Manaus FC',
            'description' => 'Equipe atualizada',
            'badge' => 'teams/atletico-fc.svg',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Atlético Manaus FC')
            ->assertJsonPath('data.description', 'Equipe atualizada');

        $this->deleteJson(route('api.v1.teams.destroy', $teamId))
            ->assertOk()
            ->assertJsonPath('message', 'Time desativado.');

        $this->assertDatabaseHas('teams', [
            'id' => $teamId,
            'is_active' => 0,
        ]);
    }

    public function test_team_show_is_public_and_team_update_is_restricted_to_owner_or_admin(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $sportMode = SportMode::query()->firstOrFail();

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Público FC',
            'description' => 'Visível publicamente',
            'badge' => 'teams/publico.svg',
        ]);

        $team->sportModes()->create(['sport_mode_id' => $sportMode->id]);

        $this->getJson(route('api.v1.teams.show', $team))
            ->assertOk()
            ->assertJsonPath('data.name', 'Público FC');

        Sanctum::actingAs($otherUser);

        $this->putJson(route('api.v1.teams.update', $team), [
            'name' => 'Tentativa Inválida',
            'description' => null,
            'badge' => null,
        ])->assertForbidden();
    }

    public function test_owner_can_add_and_remove_team_sport_mode_and_conflict_returns_domain_error(): void
    {
        $owner = User::factory()->create();
        $sportModes = SportMode::query()->take(2)->get()->values();

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Modal FC',
        ]);

        $teamSportMode = $team->sportModes()->create(['sport_mode_id' => $sportModes[0]->id]);

        Sanctum::actingAs($owner);

        $this->postJson(route('api.v1.teams.sport-modes.store', $team), [
            'sport_mode_id' => $sportModes[1]->id,
        ])->assertCreated()
            ->assertJsonPath('data.sport_mode.id', $sportModes[1]->id);

        $this->deleteJson(route('api.v1.teams.sport-modes.destroy', [$team, $teamSportMode]))
            ->assertOk();
    }
}
