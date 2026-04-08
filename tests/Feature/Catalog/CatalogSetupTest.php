<?php

namespace Tests\Feature\Catalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_tables_are_created(): void
    {
        $this->assertTrue(Schema::hasTable('sport_modes'));
        $this->assertTrue(Schema::hasTable('categories'));
        $this->assertTrue(Schema::hasTable('positions'));
        $this->assertTrue(Schema::hasTable('formations'));
        $this->assertTrue(Schema::hasTable('staff_roles'));
        $this->assertTrue(Schema::hasTable('badge_types'));
        $this->assertTrue(Schema::hasTable('sport_mode_category'));
        $this->assertTrue(Schema::hasTable('sport_mode_formation'));
        $this->assertTrue(Schema::hasTable('sport_mode_position'));
        $this->assertTrue(Schema::hasColumn('sport_modes', 'label_key'));
        $this->assertTrue(Schema::hasColumn('sport_modes', 'description_key'));
        $this->assertTrue(Schema::hasColumn('sport_modes', 'icon'));
        $this->assertFalse(Schema::hasColumn('sport_modes', 'name'));
        $this->assertTrue(Schema::hasColumn('positions', 'label_key'));
        $this->assertTrue(Schema::hasColumn('positions', 'description_key'));
        $this->assertTrue(Schema::hasColumn('positions', 'icon'));
        $this->assertFalse(Schema::hasColumn('positions', 'name'));
        $this->assertTrue(Schema::hasColumn('staff_roles', 'label_key'));
        $this->assertTrue(Schema::hasColumn('staff_roles', 'description_key'));
        $this->assertTrue(Schema::hasColumn('staff_roles', 'icon'));
        $this->assertTrue(Schema::hasColumn('badge_types', 'label_key'));
        $this->assertTrue(Schema::hasColumn('badge_types', 'description_key'));
        $this->assertFalse(Schema::hasColumn('badge_types', 'label'));
        $this->assertFalse(Schema::hasColumn('badge_types', 'description'));
    }

    public function test_catalog_seeders_populate_reference_data(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(4, DB::table('sport_modes')->count());
        $this->assertSame(4, DB::table('categories')->count());
        $this->assertSame(17, DB::table('positions')->count());
        $this->assertSame(8, DB::table('formations')->count());
        $this->assertSame(9, DB::table('staff_roles')->count());
        $this->assertSame(14, DB::table('badge_types')->count());
        $this->assertSame(16, DB::table('sport_mode_category')->count());
        $this->assertSame(16, DB::table('sport_mode_formation')->count());
        $this->assertSame(36, DB::table('sport_mode_position')->count());

        $this->assertDatabaseHas('sport_modes', [
            'key' => 'society',
            'label_key' => 'sport_modes.society.label',
            'description_key' => 'sport_modes.society.description',
            'icon' => 'shield',
        ]);

        $this->assertDatabaseHas('positions', [
            'key' => 'goleiro',
            'label_key' => 'positions.goleiro.label',
            'description_key' => 'positions.goleiro.description',
            'icon' => 'shield',
            'abbreviation' => 'GOL',
        ]);

        $this->assertDatabaseHas('staff_roles', [
            'name' => 'head_coach',
            'label_key' => 'staff_roles.head_coach.label',
            'description_key' => 'staff_roles.head_coach.description',
            'icon' => 'whistle',
        ]);

        $this->assertDatabaseHas('badge_types', [
            'name' => 'golden_ball',
            'label_key' => 'badges.golden_ball.label',
            'description_key' => 'badges.golden_ball.description',
            'icon' => 'award',
            'scope' => 'championship',
        ]);
    }
}
