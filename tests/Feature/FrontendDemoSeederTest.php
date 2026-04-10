<?php

namespace Tests\Feature;

use Database\Seeders\BadgeTypeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FormationSeeder;
use Database\Seeders\FrontendDemoSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\SportModeCategorySeeder;
use Database\Seeders\SportModeFormationSeeder;
use Database\Seeders\SportModePositionSeeder;
use Database\Seeders\SportModeSeeder;
use Database\Seeders\StaffRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Championship;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\StaffMember;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Tests\TestCase;

class FrontendDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_demo_seeder_creates_demo_accounts_and_domain_scenarios(): void
    {
        $this->seedBaseCatalogs();

        $this->seed(FrontendDemoSeeder::class);

        $admin = User::query()->where('email', 'admin@myclub.app')->firstOrFail();
        $organizer = User::query()->where('email', 'organizador@myclub.app')->firstOrFail();
        $hybrid = User::query()->where('email', 'misto.carlos@myclub.app')->firstOrFail();
        $player = User::query()->where('email', 'jogador.beatriz@myclub.app')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('teste123', $admin->password));
        $this->assertDatabaseHas('players', ['user_id' => $hybrid->id]);
        $this->assertDatabaseHas('players', ['user_id' => $player->id]);
        $this->assertDatabaseHas('staff_members', ['user_id' => User::query()->where('email', 'comissao.renato@myclub.app')->value('id')]);

        $this->assertSame(4, Team::query()->whereIn('name', [
            'Lobos FC',
            'Estrela Azul',
            'Racha Central',
            'Cidade Nova',
        ])->count());

        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Draft 2026', 'status' => 'draft']);
        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Inscricoes 2026', 'status' => 'enrollment']);
        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Ativa 2026', 'status' => 'active']);
        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Finalizada 2025', 'status' => 'finished']);
        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Arquivada 2024', 'status' => 'archived']);
        $this->assertDatabaseHas('championships', ['name' => 'Liga Demo Cancelada 2026', 'status' => 'cancelled']);

        $finishedChampionship = Championship::query()->where('name', 'Liga Demo Finalizada 2025')->firstOrFail();

        $this->assertGreaterThan(0, $finishedChampionship->awards()->count());
        $this->assertTrue(PlayerBadge::query()->where('championship_id', $finishedChampionship->id)->exists());
        $this->assertTrue(TeamInvitation::query()->whereHas('invitedUser', fn ($query) => $query->where('email', 'jogador.beatriz@myclub.app'))->exists());
    }

    public function test_frontend_demo_seeder_is_idempotent(): void
    {
        $this->seedBaseCatalogs();

        $this->seed(FrontendDemoSeeder::class);
        $this->seed(FrontendDemoSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'admin@myclub.app')->count());
        $this->assertSame(1, User::query()->where('email', 'organizador@myclub.app')->count());
        $this->assertSame(1, Team::query()->where('name', 'Lobos FC')->count());
        $this->assertSame(1, Championship::query()->where('name', 'Liga Demo Finalizada 2025')->count());
        $this->assertSame(1, StaffMember::query()->where('user_id', User::query()->where('email', 'comissao.renato@myclub.app')->value('id'))->count());
        $this->assertSame(1, Player::query()->where('user_id', User::query()->where('email', 'jogador.lucas@myclub.app')->value('id'))->count());
    }

    private function seedBaseCatalogs(): void
    {
        $this->seed([
            SportModeSeeder::class,
            CategorySeeder::class,
            PositionSeeder::class,
            FormationSeeder::class,
            StaffRoleSeeder::class,
            BadgeTypeSeeder::class,
            SportModeCategorySeeder::class,
            SportModeFormationSeeder::class,
            SportModePositionSeeder::class,
        ]);
    }
}
