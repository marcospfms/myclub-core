<?php

namespace Tests\Feature\Phase1;

use App\Models\User;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlayerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_endpoints_require_authentication(): void
    {
        $user = User::factory()->create();
        $player = Player::create([
            'user_id' => $user->id,
            'phone' => '92999990000',
        ]);

        $this->postJson(route('api.v1.players.store'), [])
            ->assertUnauthorized();

        $this->putJson(route('api.v1.players.update'), [])
            ->assertUnauthorized();

        $this->getJson(route('api.v1.players.show', $player))
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_and_update_own_player_profile(): void
    {
        Sanctum::actingAs($user = User::factory()->create());

        $storeResponse = $this->postJson(route('api.v1.players.store'), [
            'cpf' => '12345678901',
            'rg' => 'AM123456',
            'birth_date' => '2000-01-10',
            'phone' => '92999990000',
            'is_discoverable' => true,
            'history_public' => false,
            'city' => 'Manaus',
            'state' => 'Amazonas',
            'country' => 'BR',
        ]);

        $storeResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.cpf', '12345678901')
            ->assertJsonPath('data.phone', '92999990000')
            ->assertJsonPath('data.is_discoverable', true);

        $this->assertDatabaseHas('players', [
            'user_id' => $user->id,
            'cpf' => '12345678901',
            'city' => 'Manaus',
        ]);

        $updateResponse = $this->putJson(route('api.v1.players.update'), [
            'phone' => '92911112222',
            'history_public' => true,
            'city' => 'Parintins',
        ]);

        $updateResponse
            ->assertOk()
            ->assertJsonPath('data.phone', '92911112222')
            ->assertJsonPath('data.history_public', true)
            ->assertJsonPath('data.city', 'Parintins');

        $this->assertDatabaseHas('players', [
            'user_id' => $user->id,
            'phone' => '92911112222',
            'history_public' => 1,
            'city' => 'Parintins',
        ]);
    }

    public function test_player_show_hides_sensitive_fields_from_other_authenticated_users(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $player = Player::create([
            'user_id' => $owner->id,
            'cpf' => '12345678901',
            'rg' => 'AM123456',
            'birth_date' => '2000-01-10',
            'phone' => '92999990000',
            'is_discoverable' => true,
            'history_public' => true,
            'city' => 'Manaus',
            'state' => 'Amazonas',
            'country' => 'BR',
        ]);

        Sanctum::actingAs($viewer);

        $response = $this->getJson(route('api.v1.players.show', $player));

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.cpf')
            ->assertJsonMissingPath('data.rg')
            ->assertJsonMissingPath('data.phone')
            ->assertJsonPath('data.city', 'Manaus')
            ->assertJsonPath('data.history_public', true);
    }
}
