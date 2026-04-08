<?php

namespace Tests\Feature\Catalog;

use App\Enums\BadgeScope;
use App\Http\Resources\Catalog\BadgeTypeResource;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Formation;
use App\Models\Position;
use App\Models\SportMode;
use App\Models\StaffRole;
use App\Services\Catalog\BadgeTypeService;
use App\Services\Catalog\CategoryService;
use App\Services\Catalog\FormationService;
use App\Services\Catalog\PositionService;
use App\Services\Catalog\SportModeService;
use App\Services\Catalog\StaffRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CatalogModelAndServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sport_mode_model_loads_catalog_relationships(): void
    {
        $this->seed();

        $sportMode = SportMode::where('key', 'campo')
            ->with(['categories', 'formations', 'positions'])
            ->firstOrFail();

        $this->assertCount(4, $sportMode->categories);
        $this->assertCount(6, $sportMode->formations);
        $this->assertCount(13, $sportMode->positions);
    }

    public function test_badge_type_scope_is_cast_to_enum(): void
    {
        $this->seed();

        $badgeType = BadgeType::where('name', 'golden_ball')->firstOrFail();

        $this->assertInstanceOf(BadgeScope::class, $badgeType->scope);
        $this->assertSame(BadgeScope::Championship, $badgeType->scope);
    }

    public function test_badge_type_resource_returns_translation_keys_and_icon_key(): void
    {
        $this->seed();

        $badgeType = BadgeType::where('name', 'golden_ball')->firstOrFail();

        $payload = (new BadgeTypeResource($badgeType))->toArray(new Request);

        $this->assertSame('golden_ball', $payload['name']);
        $this->assertSame('badges.golden_ball.label', $payload['label_key']);
        $this->assertSame('badges.golden_ball.description', $payload['description_key']);
        $this->assertSame('award', $payload['icon']);
        $this->assertSame('championship', $payload['scope']);
    }

    public function test_catalog_services_can_create_update_list_and_delete_basic_entities(): void
    {
        $categoryService = new CategoryService;
        $positionService = new PositionService;
        $formationService = new FormationService;
        $staffRoleService = new StaffRoleService;
        $badgeTypeService = new BadgeTypeService;

        $category = $categoryService->create([
            'key' => 'adult',
            'name' => 'Adult',
        ]);

        $sportModeService = new SportModeService;

        $createdSportMode = $sportModeService->create([
            'key' => 'indoor',
            'label_key' => 'sport_modes.indoor.label',
            'description_key' => 'sport_modes.indoor.description',
            'icon' => 'shield',
        ]);

        $position = $positionService->create([
            'key' => 'playmaker',
            'label_key' => 'positions.playmaker.label',
            'description_key' => 'positions.playmaker.description',
            'icon' => 'sparkles',
            'abbreviation' => 'PLY',
        ]);

        $formation = $formationService->create([
            'key' => '5-3-2',
            'name' => '5-3-2',
        ]);

        $staffRole = $staffRoleService->create([
            'name' => 'nutritionist',
            'label_key' => 'staff_roles.nutritionist.label',
            'description_key' => 'staff_roles.nutritionist.description',
            'icon' => 'briefcase',
        ]);

        $badgeType = $badgeTypeService->create([
            'name' => 'super_sub',
            'label_key' => 'badges.super_sub.label',
            'description_key' => 'badges.super_sub.description',
            'icon' => 'sparkles',
            'scope' => BadgeScope::Career,
        ]);

        $this->assertModelExists($category);
        $this->assertModelExists($createdSportMode);
        $this->assertModelExists($position);
        $this->assertModelExists($formation);
        $this->assertModelExists($staffRole);
        $this->assertModelExists($badgeType);

        $updatedSportMode = $sportModeService->update($createdSportMode, [
            'label_key' => 'sport_modes.indoor_pro.label',
            'description_key' => 'sport_modes.indoor_pro.description',
            'icon' => 'star',
        ]);
        $this->assertSame('sport_modes.indoor_pro.label', $updatedSportMode->label_key);
        $this->assertSame('Adult Open', $categoryService->update($category, ['name' => 'Adult Open'])->name);
        $updatedPosition = $positionService->update($position, [
            'label_key' => 'positions.creator_midfielder.label',
            'description_key' => 'positions.creator_midfielder.description',
            'icon' => 'zap',
            'abbreviation' => 'PLY',
        ]);
        $this->assertSame('positions.creator_midfielder.label', $updatedPosition->label_key);
        $this->assertSame('5-4-1', $formationService->update($formation, ['key' => '5-4-1', 'name' => '5-4-1'])->name);
        $updatedStaffRole = $staffRoleService->update($staffRole, [
            'name' => 'team_manager',
            'label_key' => 'staff_roles.team_manager.label',
            'description_key' => 'staff_roles.team_manager.description',
            'icon' => 'users',
        ]);
        $this->assertSame('team_manager', $updatedStaffRole->name);
        $updatedBadgeType = $badgeTypeService->update($badgeType, [
            'name' => 'impact_player',
            'label_key' => 'badges.impact_player.label',
            'description_key' => 'badges.impact_player.description',
            'icon' => 'zap',
        ]);
        $this->assertSame('impact_player', $updatedBadgeType->name);
        $this->assertSame('badges.impact_player.label', $updatedBadgeType->label_key);
        $this->assertSame('badges.impact_player.description', $updatedBadgeType->description_key);
        $this->assertSame('zap', $updatedBadgeType->icon);

        $this->assertCount(1, $sportModeService->listAll());
        $this->assertCount(1, $categoryService->listAll());
        $this->assertCount(1, $positionService->listAll());
        $this->assertCount(1, $formationService->listAll());
        $this->assertCount(1, $staffRoleService->listAll());
        $this->assertCount(1, $badgeTypeService->listAll());

        $sportModeService->delete($createdSportMode);
        $categoryService->delete($category);
        $positionService->delete($position);
        $formationService->delete($formation);
        $staffRoleService->delete($staffRole);
        $badgeTypeService->delete($badgeType);

        $this->assertModelMissing($createdSportMode);
        $this->assertModelMissing($category);
        $this->assertModelMissing($position);
        $this->assertModelMissing($formation);
        $this->assertModelMissing($staffRole);
        $this->assertModelMissing($badgeType);
    }

    public function test_sport_mode_service_can_create_update_and_sync_catalog_links(): void
    {
        $categoryA = Category::create(['key' => 'under_13', 'name' => 'Under-13']);
        $categoryB = Category::create(['key' => 'under_15', 'name' => 'Under-15']);
        $formation = Formation::create(['key' => '4-2-3-1', 'name' => '4-2-3-1']);
        $position = Position::create([
            'key' => 'wing_back',
            'label_key' => 'positions.wing_back.label',
            'description_key' => 'positions.wing_back.description',
            'icon' => 'shield',
            'abbreviation' => 'WBK',
        ]);

        $sportModeService = new SportModeService;

        $sportMode = $sportModeService->create([
            'key' => 'indoor',
            'label_key' => 'sport_modes.indoor.label',
            'description_key' => 'sport_modes.indoor.description',
            'icon' => 'shield',
        ]);

        $sportModeService->syncCategories($sportMode, [$categoryA->id, $categoryB->id]);
        $sportModeService->syncFormations($sportMode, [$formation->id]);
        $sportModeService->syncPositions($sportMode, [$position->id]);

        $loadedSportMode = $sportModeService->listAll()->first();

        $this->assertSame('indoor', $loadedSportMode->key);
        $this->assertCount(2, $loadedSportMode->categories);
        $this->assertCount(1, $loadedSportMode->formations);
        $this->assertCount(1, $loadedSportMode->positions);

        $updatedSportMode = $sportModeService->update($sportMode, [
            'label_key' => 'sport_modes.indoor_pro.label',
            'description_key' => 'sport_modes.indoor_pro.description',
            'icon' => 'star',
        ]);

        $this->assertSame('sport_modes.indoor_pro.label', $updatedSportMode->label_key);
        $this->assertSame('sport_modes.indoor_pro.description', $updatedSportMode->description_key);
        $this->assertSame('star', $updatedSportMode->icon);

        $sportModeService->delete($sportMode);

        $this->assertModelMissing($sportMode);
    }

    public function test_seeded_sport_modes_positions_and_staff_roles_use_translation_keys_and_icon_keys(): void
    {
        $this->seed();

        $sportMode = SportMode::where('key', 'campo')->firstOrFail();
        $position = Position::where('key', 'goleiro')->firstOrFail();
        $staffRole = StaffRole::where('name', 'head_coach')->firstOrFail();

        $this->assertSame('sport_modes.campo.label', $sportMode->label_key);
        $this->assertSame('sport_modes.campo.description', $sportMode->description_key);
        $this->assertSame('map', $sportMode->icon);

        $this->assertSame('positions.goleiro.label', $position->label_key);
        $this->assertSame('positions.goleiro.description', $position->description_key);
        $this->assertSame('shield', $position->icon);

        $this->assertSame('staff_roles.head_coach.label', $staffRole->label_key);
        $this->assertSame('staff_roles.head_coach.description', $staffRole->description_key);
        $this->assertSame('whistle', $staffRole->icon);
    }
}
