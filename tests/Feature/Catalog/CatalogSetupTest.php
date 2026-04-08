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
    }

    public function test_catalog_seeders_populate_reference_data(): void
    {
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
            'name' => 'Society',
        ]);

        $this->assertDatabaseHas('positions', [
            'key' => 'goleiro',
            'abbreviation' => 'GOL',
        ]);

        $this->assertDatabaseHas('badge_types', [
            'name' => 'golden_ball',
            'scope' => 'championship',
        ]);
    }
}
