<?php

namespace Tests\Feature\Catalog;

use App\Enums\BadgeScope;
use App\Http\Requests\Catalog\StoreBadgeTypeRequest;
use App\Http\Requests\Catalog\StoreCategoryRequest;
use App\Http\Requests\Catalog\StoreFormationRequest;
use App\Http\Requests\Catalog\StorePositionRequest;
use App\Http\Requests\Catalog\StoreSportModeRequest;
use App\Http\Requests\Catalog\StoreStaffRoleRequest;
use App\Http\Requests\Catalog\UpdateBadgeTypeRequest;
use App\Http\Requests\Catalog\UpdateCategoryRequest;
use App\Http\Requests\Catalog\UpdateFormationRequest;
use App\Http\Requests\Catalog\UpdatePositionRequest;
use App\Http\Requests\Catalog\UpdateSportModeRequest;
use App\Http\Requests\Catalog\UpdateStaffRoleRequest;
use App\Http\Resources\Catalog\BadgeTypeResource;
use App\Http\Resources\Catalog\CategoryResource;
use App\Http\Resources\Catalog\FormationResource;
use App\Http\Resources\Catalog\PositionResource;
use App\Http\Resources\Catalog\SportModeResource;
use App\Http\Resources\Catalog\StaffRoleResource;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Formation;
use App\Models\Position;
use App\Models\SportMode;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CatalogRequestAndResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeRouteParameterResolver(string $parameter, mixed $value): object
    {
        return new class($parameter, $value)
        {
            public function __construct(
                private readonly string $parameter,
                private readonly mixed $value,
            ) {}

            public function parameter(string $name, mixed $default = null): mixed
            {
                return $name === $this->parameter ? $this->value : $default;
            }
        };
    }

    private function setUserResolver(object $request, ?User $user): void
    {
        $request->setUserResolver(fn () => $user);
    }

    public function test_store_requests_validate_catalog_creation_payloads(): void
    {
        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);
        $formation = Formation::create(['key' => '4-4-2', 'name' => '4-4-2']);
        $position = Position::create([
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);

        $this->assertFalse(Validator::make([], (new StoreSportModeRequest)->rules())->passes());
        $this->assertTrue(Validator::make([
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
            'description_key' => 'sport_modes.campo.description',
            'icon' => 'map',
            'category_ids' => [$category->id],
            'formation_ids' => [$formation->id],
            'position_ids' => [$position->id],
        ], (new StoreSportModeRequest)->rules())->passes());

        $this->assertTrue(Validator::make([
            'key' => 'sub_20',
            'name' => 'Sub-20',
        ], (new StoreCategoryRequest)->rules())->passes());

        $this->assertTrue(Validator::make([
            'key' => '3-4-3',
            'name' => '3-4-3',
        ], (new StoreFormationRequest)->rules())->passes());

        $this->assertFalse(Validator::make([
            'key' => 'armador',
            'label_key' => 'positions.armador.label',
            'abbreviation' => 'AR',
        ], (new StorePositionRequest)->rules())->passes());

        $this->assertTrue(Validator::make([
            'key' => 'armador',
            'label_key' => 'positions.armador.label',
            'description_key' => 'positions.armador.description',
            'icon' => 'sparkles',
            'abbreviation' => 'ARM',
        ], (new StorePositionRequest)->rules())->passes());

        $this->assertTrue(Validator::make([
            'name' => 'assistant_coach',
            'label_key' => 'staff_roles.assistant_coach.label',
            'description_key' => 'staff_roles.assistant_coach.description',
            'icon' => 'users',
        ], (new StoreStaffRoleRequest)->rules())->passes());

        $this->assertFalse(Validator::make([
            'name' => 'best_player',
            'label_key' => 'badges.best_player.label',
            'scope' => 'invalid',
        ], (new StoreBadgeTypeRequest)->rules())->passes());

        $this->assertTrue(Validator::make([
            'name' => 'best_player',
            'label_key' => 'badges.best_player.label',
            'description_key' => 'badges.best_player.description',
            'icon' => 'award',
            'scope' => BadgeScope::Championship->value,
        ], (new StoreBadgeTypeRequest)->rules())->passes());
    }

    public function test_update_requests_ignore_current_unique_values(): void
    {
        $sportMode = SportMode::create([
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
            'description_key' => 'sport_modes.campo.description',
            'icon' => 'map',
        ]);
        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);
        $formation = Formation::create(['key' => '4-4-2', 'name' => '4-4-2']);
        $position = Position::create([
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);
        $staffRole = StaffRole::create([
            'name' => 'head_coach',
            'label_key' => 'staff_roles.head_coach.label',
            'description_key' => 'staff_roles.head_coach.description',
            'icon' => 'whistle',
        ]);
        $badgeType = BadgeType::create([
            'name' => 'golden_ball',
            'label_key' => 'badges.golden_ball.label',
            'description_key' => 'badges.golden_ball.description',
            'icon' => 'award',
            'scope' => BadgeScope::Championship,
        ]);

        $updateSportModeRequest = UpdateSportModeRequest::create('/', 'PUT', [
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
        ]);
        $updateSportModeRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('sport_mode', $sportMode));

        $updateCategoryRequest = UpdateCategoryRequest::create('/', 'PUT', [
            'key' => 'adult',
            'name' => 'Adult',
        ]);
        $updateCategoryRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('category', $category));

        $updatePositionRequest = UpdatePositionRequest::create('/', 'PUT', [
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'abbreviation' => 'GOL',
        ]);
        $updatePositionRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('position', $position));

        $updateFormationRequest = UpdateFormationRequest::create('/', 'PUT', [
            'key' => '4-4-2',
            'name' => '4-4-2',
        ]);
        $updateFormationRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('formation', $formation));

        $updateStaffRoleRequest = UpdateStaffRoleRequest::create('/', 'PUT', [
            'name' => 'head_coach',
            'label_key' => 'staff_roles.head_coach.label',
        ]);
        $updateStaffRoleRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('staff_role', $staffRole));

        $updateBadgeTypeRequest = UpdateBadgeTypeRequest::create('/', 'PUT', [
            'name' => 'golden_ball',
            'label_key' => 'badges.golden_ball.label',
            'scope' => BadgeScope::Championship->value,
        ]);
        $updateBadgeTypeRequest->setRouteResolver(fn (): object => $this->makeRouteParameterResolver('badge_type', $badgeType));

        $this->assertTrue(Validator::make($updateSportModeRequest->all(), $updateSportModeRequest->rules())->passes());
        $this->assertTrue(Validator::make($updateCategoryRequest->all(), $updateCategoryRequest->rules())->passes());
        $this->assertTrue(Validator::make($updatePositionRequest->all(), $updatePositionRequest->rules())->passes());
        $this->assertTrue(Validator::make($updateFormationRequest->all(), $updateFormationRequest->rules())->passes());
        $this->assertTrue(Validator::make($updateStaffRoleRequest->all(), $updateStaffRoleRequest->rules())->passes());
        $this->assertTrue(Validator::make($updateBadgeTypeRequest->all(), $updateBadgeTypeRequest->rules())->passes());
    }

    public function test_catalog_resources_return_expected_payload_shapes(): void
    {
        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);
        $formation = Formation::create(['key' => '4-4-2', 'name' => '4-4-2']);
        $position = Position::create([
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);
        $staffRole = StaffRole::create([
            'name' => 'head_coach',
            'label_key' => 'staff_roles.head_coach.label',
            'description_key' => 'staff_roles.head_coach.description',
            'icon' => 'whistle',
        ]);
        $badgeType = BadgeType::create([
            'name' => 'golden_ball',
            'label_key' => 'badges.golden_ball.label',
            'description_key' => 'badges.golden_ball.description',
            'icon' => 'award',
            'scope' => BadgeScope::Championship,
        ]);
        $sportMode = SportMode::create([
            'key' => 'campo',
            'label_key' => 'sport_modes.campo.label',
            'description_key' => 'sport_modes.campo.description',
            'icon' => 'map',
        ]);

        $sportMode->categories()->sync([$category->id]);
        $sportMode->formations()->sync([$formation->id]);
        $sportMode->positions()->sync([$position->id]);
        $sportMode->load(['categories', 'formations', 'positions']);

        $request = new Request;

        $categoryPayload = (new CategoryResource($category))->toArray($request);
        $formationPayload = (new FormationResource($formation))->toArray($request);
        $positionPayload = (new PositionResource($position))->toArray($request);
        $staffRolePayload = (new StaffRoleResource($staffRole))->toArray($request);
        $badgeTypePayload = (new BadgeTypeResource($badgeType))->toArray($request);
        $sportModePayload = (new SportModeResource($sportMode))->toArray($request);

        $this->assertSame(['id', 'key', 'name', 'created_at', 'updated_at'], array_keys($categoryPayload));
        $this->assertSame(['id', 'key', 'name', 'created_at', 'updated_at'], array_keys($formationPayload));
        $this->assertSame(['id', 'key', 'label_key', 'description_key', 'icon', 'abbreviation', 'created_at', 'updated_at'], array_keys($positionPayload));
        $this->assertSame(['id', 'name', 'label_key', 'description_key', 'icon', 'created_at', 'updated_at'], array_keys($staffRolePayload));
        $this->assertSame(['id', 'name', 'label_key', 'description_key', 'icon', 'scope', 'created_at', 'updated_at'], array_keys($badgeTypePayload));
        $this->assertSame(['id', 'key', 'label_key', 'description_key', 'icon', 'categories', 'formations', 'positions', 'created_at', 'updated_at'], array_keys($sportModePayload));

        $this->assertCount(1, $sportModePayload['categories']);
        $this->assertCount(1, $sportModePayload['formations']);
        $this->assertCount(1, $sportModePayload['positions']);
        $this->assertSame('positions.goleiro.label', $sportModePayload['positions'][0]['label_key']);
        $this->assertSame('championship', $badgeTypePayload['scope']);
    }

    public function test_catalog_write_requests_allow_only_admin_users(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $requests = [
            new StoreSportModeRequest,
            new StoreCategoryRequest,
            new StorePositionRequest,
            new StoreFormationRequest,
            new StoreStaffRoleRequest,
            new StoreBadgeTypeRequest,
        ];

        foreach ($requests as $request) {
            $this->setUserResolver($request, $admin);
            $this->assertTrue($request->authorize());

            $this->setUserResolver($request, $user);
            $this->assertFalse($request->authorize());

            $this->setUserResolver($request, null);
            $this->assertFalse($request->authorize());
        }
    }
}
