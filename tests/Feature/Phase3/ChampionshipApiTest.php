<?php

namespace Tests\Feature\Phase3;

use App\Models\Championship;
use App\Models\SportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChampionshipApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_championship(): void
    {
        $user = User::factory()->create();
        $sportMode = SportMode::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.championships.store'), [
            'name' => 'Campeonato Verao',
            'format' => 'league',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.format', 'league');
    }

    public function test_only_league_format_is_allowed_in_phase_three(): void
    {
        $user = User::factory()->create();
        $sportMode = SportMode::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.championships.store'), [
            'name' => 'Copa Bairro',
            'format' => 'knockout',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertUnprocessable();
    }

    public function test_creator_can_open_enrollment(): void
    {
        $user = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $championship = Championship::factory()
            ->draft()
            ->for($user, 'creator')
            ->withSportMode($sportMode)
            ->create();

        Sanctum::actingAs($user);

        $this->postJson(route('api.v1.championships.open-enrollment', $championship))
            ->assertOk()
            ->assertJsonPath('data.status', 'enrollment');
    }

    public function test_non_creator_cannot_open_enrollment(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $championship = Championship::factory()
            ->draft()
            ->for($creator, 'creator')
            ->withSportMode($sportMode)
            ->create();

        Sanctum::actingAs($otherUser);

        $this->postJson(route('api.v1.championships.open-enrollment', $championship))
            ->assertForbidden();
    }

    public function test_admin_can_cancel_active_championship(): void
    {
        $admin = User::factory()->admin()->create();
        $championship = Championship::factory()->active()->create();

        Sanctum::actingAs($admin);

        $this->postJson(route('api.v1.championships.cancel', $championship))
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }
}
