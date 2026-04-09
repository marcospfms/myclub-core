<?php

namespace Tests\Feature\Catalog;

use App\Enums\BadgeScope;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Formation;
use App\Models\Position;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AdminSimpleCatalogCrudTest extends TestCase
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

    public function test_admin_can_crud_categories(): void
    {
        $admin = $this->adminUser();
        $category = Category::create(['key' => 'adult', 'name' => 'Adult']);

        $this->actingAs($admin)
            ->get(route('admin.catalog.categories.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/catalog/categories/Index')
                ->has('categories', 1));

        $this->actingAs($admin)
            ->post(route('admin.catalog.categories.store'), [
                'key' => 'junior',
                'name' => 'Junior',
            ])
            ->assertRedirect(route('admin.catalog.categories.index'));

        $this->assertDatabaseHas('categories', [
            'key' => 'junior',
            'name' => 'Junior',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.categories.update', $category), [
                'key' => 'adult_open',
                'name' => 'Adult Open',
            ])
            ->assertRedirect(route('admin.catalog.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'key' => 'adult_open',
            'name' => 'Adult Open',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.categories.destroy', $category))
            ->assertRedirect(route('admin.catalog.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_can_crud_positions(): void
    {
        $admin = $this->adminUser();
        $position = Position::create([
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.catalog.positions.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/catalog/positions/Index')
                ->has('positions', 1));

        $this->actingAs($admin)
            ->post(route('admin.catalog.positions.store'), [
                'key' => 'volante',
                'label_key' => 'positions.volante.label',
                'description_key' => 'positions.volante.description',
                'icon' => 'shield',
                'abbreviation' => 'VOL',
            ])
            ->assertRedirect(route('admin.catalog.positions.index'));

        $this->assertDatabaseHas('positions', [
            'key' => 'volante',
            'abbreviation' => 'VOL',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.positions.update', $position), [
                'key' => 'zagueiro',
                'label_key' => 'positions.zagueiro.label',
                'description_key' => 'positions.zagueiro.description',
                'icon' => 'shield',
                'abbreviation' => 'ZAG',
            ])
            ->assertRedirect(route('admin.catalog.positions.index'));

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'key' => 'zagueiro',
            'abbreviation' => 'ZAG',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.positions.destroy', $position))
            ->assertRedirect(route('admin.catalog.positions.index'));

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
    }

    public function test_admin_can_crud_formations(): void
    {
        $admin = $this->adminUser();
        $formation = Formation::create(['key' => '4-4-2', 'name' => '4-4-2']);

        $this->actingAs($admin)
            ->get(route('admin.catalog.formations.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/catalog/formations/Index')
                ->has('formations', 1));

        $this->actingAs($admin)
            ->post(route('admin.catalog.formations.store'), [
                'key' => '3-5-2',
                'name' => '3-5-2',
            ])
            ->assertRedirect(route('admin.catalog.formations.index'));

        $this->assertDatabaseHas('formations', [
            'key' => '3-5-2',
            'name' => '3-5-2',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.formations.update', $formation), [
                'key' => '4-3-3',
                'name' => '4-3-3',
            ])
            ->assertRedirect(route('admin.catalog.formations.index'));

        $this->assertDatabaseHas('formations', [
            'id' => $formation->id,
            'key' => '4-3-3',
            'name' => '4-3-3',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.formations.destroy', $formation))
            ->assertRedirect(route('admin.catalog.formations.index'));

        $this->assertDatabaseMissing('formations', ['id' => $formation->id]);
    }

    public function test_admin_can_crud_staff_roles(): void
    {
        $admin = $this->adminUser();
        $staffRole = StaffRole::create([
            'name' => 'head_coach',
            'label_key' => 'staff_roles.head_coach.label',
            'description_key' => 'staff_roles.head_coach.description',
            'icon' => 'whistle',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.catalog.staff-roles.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/catalog/staff-roles/Index')
                ->has('staffRoles', 1));

        $this->actingAs($admin)
            ->post(route('admin.catalog.staff-roles.store'), [
                'name' => 'assistant_coach',
                'label_key' => 'staff_roles.assistant_coach.label',
                'description_key' => 'staff_roles.assistant_coach.description',
                'icon' => 'users',
            ])
            ->assertRedirect(route('admin.catalog.staff-roles.index'));

        $this->assertDatabaseHas('staff_roles', [
            'name' => 'assistant_coach',
            'label_key' => 'staff_roles.assistant_coach.label',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.staff-roles.update', $staffRole), [
                'name' => 'team_manager',
                'label_key' => 'staff_roles.team_manager.label',
                'description_key' => 'staff_roles.team_manager.description',
                'icon' => 'briefcase',
            ])
            ->assertRedirect(route('admin.catalog.staff-roles.index'));

        $this->assertDatabaseHas('staff_roles', [
            'id' => $staffRole->id,
            'name' => 'team_manager',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.staff-roles.destroy', $staffRole))
            ->assertRedirect(route('admin.catalog.staff-roles.index'));

        $this->assertDatabaseMissing('staff_roles', ['id' => $staffRole->id]);
    }

    public function test_admin_can_crud_badge_types(): void
    {
        $admin = $this->adminUser();
        $badgeType = BadgeType::create([
            'name' => 'golden_ball',
            'label_key' => 'badges.golden_ball.label',
            'description_key' => 'badges.golden_ball.description',
            'icon' => 'award',
            'scope' => BadgeScope::Championship,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.catalog.badge-types.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/catalog/badge-types/Index')
                ->has('badgeTypes', 1));

        $this->actingAs($admin)
            ->post(route('admin.catalog.badge-types.store'), [
                'name' => 'best_goalkeeper',
                'label_key' => 'badges.best_goalkeeper.label',
                'description_key' => 'badges.best_goalkeeper.description',
                'icon' => 'shield',
                'scope' => BadgeScope::Championship->value,
            ])
            ->assertRedirect(route('admin.catalog.badge-types.index'));

        $this->assertDatabaseHas('badge_types', [
            'name' => 'best_goalkeeper',
            'scope' => BadgeScope::Championship->value,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.badge-types.update', $badgeType), [
                'name' => 'golden_glove',
                'label_key' => 'badges.golden_glove.label',
                'description_key' => 'badges.golden_glove.description',
                'icon' => 'shield',
                'scope' => BadgeScope::Career->value,
            ])
            ->assertRedirect(route('admin.catalog.badge-types.index'));

        $this->assertDatabaseHas('badge_types', [
            'id' => $badgeType->id,
            'name' => 'golden_glove',
            'scope' => BadgeScope::Career->value,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.badge-types.destroy', $badgeType))
            ->assertRedirect(route('admin.catalog.badge-types.index'));

        $this->assertDatabaseMissing('badge_types', ['id' => $badgeType->id]);
    }
}
