<?php

namespace Tests\Feature\Catalog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogApiResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_sport_modes_api_returns_nested_catalog_payload(): void
    {
        $this->seed();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('api.v1.catalog.sport-modes.index'));

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Sport modes retrieved.')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'key',
                        'label_key',
                        'description_key',
                        'icon',
                        'categories',
                        'formations',
                        'positions',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'message',
            ]);
    }

    public function test_simple_catalog_endpoints_return_expected_payload_shapes(): void
    {
        $this->seed();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson(route('api.v1.catalog.categories.index'))
            ->assertOk()
            ->assertJsonPath('data.0.key', 'livre')
            ->assertJsonStructure([
                'data' => [['id', 'key', 'name', 'created_at', 'updated_at']],
            ]);

        $this->getJson(route('api.v1.catalog.positions.index'))
            ->assertOk()
            ->assertJsonPath('data.0.key', 'ala_direito')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'key',
                    'label_key',
                    'description_key',
                    'icon',
                    'abbreviation',
                    'created_at',
                    'updated_at',
                ]],
            ]);

        $this->getJson(route('api.v1.catalog.formations.index'))
            ->assertOk()
            ->assertJsonPath('data.0.key', '1-2-1')
            ->assertJsonStructure([
                'data' => [['id', 'key', 'name', 'created_at', 'updated_at']],
            ]);

        $this->getJson(route('api.v1.catalog.staff-roles.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'analyst')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'label_key',
                    'description_key',
                    'icon',
                    'created_at',
                    'updated_at',
                ]],
            ]);

        $this->getJson(route('api.v1.catalog.badge-types.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'best_assist')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'name',
                    'label_key',
                    'description_key',
                    'icon',
                    'scope',
                    'created_at',
                    'updated_at',
                ]],
            ]);
    }
}
