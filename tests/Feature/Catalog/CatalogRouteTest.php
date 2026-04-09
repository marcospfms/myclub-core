<?php

namespace Tests\Feature\Catalog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_catalog_api_routes_require_authentication(): void
    {
        $this->seed();

        $routes = [
            route('api.v1.catalog.sport-modes.index'),
            route('api.v1.catalog.categories.index'),
            route('api.v1.catalog.positions.index'),
            route('api.v1.catalog.formations.index'),
            route('api.v1.catalog.staff-roles.index'),
            route('api.v1.catalog.badge-types.index'),
        ];

        foreach ($routes as $route) {
            $this->getJson($route)->assertUnauthorized();
        }
    }

    public function test_authenticated_users_can_list_catalog_api_routes(): void
    {
        $this->seed();
        Sanctum::actingAs(User::factory()->create());

        $responses = [
            $this->getJson(route('api.v1.catalog.sport-modes.index')),
            $this->getJson(route('api.v1.catalog.categories.index')),
            $this->getJson(route('api.v1.catalog.positions.index')),
            $this->getJson(route('api.v1.catalog.formations.index')),
            $this->getJson(route('api.v1.catalog.staff-roles.index')),
            $this->getJson(route('api.v1.catalog.badge-types.index')),
        ];

        foreach ($responses as $response) {
            $response
                ->assertOk()
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message',
                ])
                ->assertJsonPath('success', true);
        }
    }

    public function test_admin_catalog_routes_require_authentication(): void
    {
        $routes = [
            route('admin.catalog.sport-modes.index'),
            route('admin.catalog.categories.index'),
            route('admin.catalog.positions.index'),
            route('admin.catalog.formations.index'),
            route('admin.catalog.staff-roles.index'),
            route('admin.catalog.badge-types.index'),
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertRedirect(route('login'));
        }
    }

    public function test_non_admin_users_cannot_access_admin_catalog_routes(): void
    {
        $user = User::factory()->create();

        $routes = [
            route('admin.catalog.sport-modes.index'),
            route('admin.catalog.categories.index'),
            route('admin.catalog.positions.index'),
            route('admin.catalog.formations.index'),
            route('admin.catalog.staff-roles.index'),
            route('admin.catalog.badge-types.index'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)->get($route)->assertForbidden();
        }
    }

    public function test_admin_users_can_access_admin_catalog_index_routes(): void
    {
        $this->seed();
        $admin = User::factory()->admin()->create();

        $routes = [
            route('admin.catalog.sport-modes.index'),
            route('admin.catalog.categories.index'),
            route('admin.catalog.positions.index'),
            route('admin.catalog.formations.index'),
            route('admin.catalog.staff-roles.index'),
            route('admin.catalog.badge-types.index'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get($route)->assertOk();
        }
    }
}
