<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Formation;
use App\Models\Position;
use App\Models\SportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminSportModeCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function adminUser(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_list_sport_modes(): void
    {
        $this->seed();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.catalog.sport-modes.index'));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/catalog/sport-modes/Index')
            ->has('sportModes.data', 4)
            ->where('sportModes.data.0.key', 'areia'));
    }

    public function test_admin_can_render_create_sport_mode_screen(): void
    {
        $this->seed();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.catalog.sport-modes.create'));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/catalog/sport-modes/Create')
            ->has('categories.data', 4)
            ->has('formations.data')
            ->has('positions.data'));
    }

    public function test_admin_can_create_sport_mode_with_catalog_links(): void
    {
        $admin = $this->adminUser();
        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);
        $formation = Formation::create(['key' => '4-4-2', 'name' => '4-4-2']);
        $position = Position::create([
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.catalog.sport-modes.store'), [
            'key' => 'fut7',
            'label_key' => 'sport_modes.fut7.label',
            'description_key' => 'sport_modes.fut7.description',
            'icon' => 'map',
            'category_ids' => [$category->id],
            'formation_ids' => [$formation->id],
            'position_ids' => [$position->id],
        ]);

        $sportMode = SportMode::where('key', 'fut7')->firstOrFail();

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseHas('sport_modes', [
            'id' => $sportMode->id,
            'key' => 'fut7',
            'label_key' => 'sport_modes.fut7.label',
        ]);
        $this->assertDatabaseHas('sport_mode_category', [
            'sport_mode_id' => $sportMode->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('sport_mode_formation', [
            'sport_mode_id' => $sportMode->id,
            'formation_id' => $formation->id,
        ]);
        $this->assertDatabaseHas('sport_mode_position', [
            'sport_mode_id' => $sportMode->id,
            'position_id' => $position->id,
        ]);
    }

    public function test_admin_can_render_edit_sport_mode_screen(): void
    {
        $this->seed();
        $sportMode = SportMode::query()->where('key', 'campo')->firstOrFail();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.catalog.sport-modes.edit', $sportMode));

        $response->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/catalog/sport-modes/Edit')
            ->where('sportMode.data.id', $sportMode->id)
            ->has('sportMode.data.categories')
            ->has('categories.data')
            ->has('formations.data')
            ->has('positions.data'));
    }

    public function test_admin_can_update_sport_mode_and_sync_links(): void
    {
        $admin = $this->adminUser();
        $sportMode = SportMode::create([
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
            'description_key' => 'sport_modes.campo.description',
            'icon' => 'map',
        ]);

        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);
        $formation = Formation::create(['key' => '4-3-3', 'name' => '4-3-3']);
        $position = Position::create([
            'key' => 'zagueiro',
            'label_key' => 'positions.zagueiro.label',
            'description_key' => 'positions.zagueiro.description',
            'icon' => 'shield',
            'abbreviation' => 'ZAG',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.catalog.sport-modes.update', $sportMode), [
            'key' => 'campo_pro',
            'label_key' => 'sport_modes.campo_pro.label',
            'description_key' => 'sport_modes.campo_pro.description',
            'icon' => 'star',
            'category_ids' => [$category->id],
            'formation_ids' => [$formation->id],
            'position_ids' => [$position->id],
        ]);

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseHas('sport_modes', [
            'id' => $sportMode->id,
            'key' => 'campo_pro',
            'label_key' => 'sport_modes.campo_pro.label',
            'icon' => 'star',
        ]);
        $this->assertDatabaseHas('sport_mode_category', [
            'sport_mode_id' => $sportMode->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('sport_mode_formation', [
            'sport_mode_id' => $sportMode->id,
            'formation_id' => $formation->id,
        ]);
        $this->assertDatabaseHas('sport_mode_position', [
            'sport_mode_id' => $sportMode->id,
            'position_id' => $position->id,
        ]);
    }

    public function test_admin_can_delete_sport_mode(): void
    {
        $admin = $this->adminUser();
        $sportMode = SportMode::create([
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
            'description_key' => 'sport_modes.campo.description',
            'icon' => 'map',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.catalog.sport-modes.destroy', $sportMode));

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseMissing('sport_modes', [
            'id' => $sportMode->id,
        ]);
    }

    public function test_sport_mode_requires_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser())
            ->post(route('admin.catalog.sport-modes.store'), []);

        $response->assertSessionHasErrors([
            'key',
            'label_key',
        ]);
    }
}
