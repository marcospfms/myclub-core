<?php

namespace Database\Seeders;

use App\Enums\BadgeScope;
use App\Enums\ChampionshipStatus;
use App\Enums\InvitationStatus;
use App\Enums\MatchStatus;
use App\Models\BadgeType;
use App\Models\Category;
use App\Models\Championship;
use App\Models\ChampionshipAward;
use App\Models\ChampionshipGroup;
use App\Models\ChampionshipMatch;
use App\Models\ChampionshipMatchHighlight;
use App\Models\ChampionshipPhase;
use App\Models\ChampionshipRound;
use App\Models\Player;
use App\Models\PlayerBadge;
use App\Models\PlayerMembership;
use App\Models\Position;
use App\Models\SportMode;
use App\Models\StaffMember;
use App\Models\StaffRole;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamSportMode;
use App\Models\TeamStaff;
use App\Models\User;
use App\Services\Championship\ChampionshipClosingService;
use App\Services\Championship\ChampionshipEnrollmentService;
use App\Services\Championship\ChampionshipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontendDemoSeeder extends Seeder
{
    private const PASSWORD = 'teste123';

    /**
     * @var array<int, string>
     */
    private const DEMO_TEAM_NAMES = [
        'Lobos FC',
        'Estrela Azul',
        'Racha Central',
        'Cidade Nova',
    ];

    /**
     * @var array<int, string>
     */
    private const DEMO_CHAMPIONSHIP_NAMES = [
        'Liga Demo Draft 2026',
        'Liga Demo Inscricoes 2026',
        'Liga Demo Ativa 2026',
        'Liga Demo Finalizada 2025',
        'Liga Demo Arquivada 2024',
        'Liga Demo Cancelada 2026',
    ];

    public function run(): void
    {
        $users = $this->seedUsers();

        $this->cleanupPreviousDemoData();

        $refs = $this->loadReferences();
        $teams = $this->seedTeams($users, $refs);
        $this->seedPlayersAndMemberships($users, $teams, $refs);
        $this->seedStaff($users, $teams, $refs);
        $this->seedInvitations($users, $teams, $refs);
        $this->seedChampionships($users, $teams, $refs);
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        return [
            'admin' => $this->upsertUser('admin@myclub.app', 'MyClub Admin', 'admin'),
            'organizer' => $this->upsertUser('organizador@myclub.app', 'Organizador Demo', 'user'),
            'owner_alpha' => $this->upsertUser('dono.alpha@myclub.app', 'Dono Alpha', 'user'),
            'owner_beta' => $this->upsertUser('dono.beta@myclub.app', 'Dono Beta', 'user'),
            'hybrid_carlos' => $this->upsertUser('misto.carlos@myclub.app', 'Carlos Misto', 'user'),
            'hybrid_marina' => $this->upsertUser('misto.marina@myclub.app', 'Marina Mista', 'user'),
            'player_lucas' => $this->upsertUser('jogador.lucas@myclub.app', 'Lucas Jogador', 'user'),
            'player_beatriz' => $this->upsertUser('jogador.beatriz@myclub.app', 'Beatriz Jogadora', 'user'),
            'support_gabriel' => $this->upsertUser('apoio.gabriel@myclub.app', 'Gabriel Elenco', 'user'),
            'staff_renato' => $this->upsertUser('comissao.renato@myclub.app', 'Renato Staff', 'user'),
        ];
    }

    private function cleanupPreviousDemoData(): void
    {
        $championshipIds = Championship::query()
            ->whereIn('name', self::DEMO_CHAMPIONSHIP_NAMES)
            ->pluck('id');

        if ($championshipIds->isNotEmpty()) {
            PlayerBadge::query()->whereIn('championship_id', $championshipIds)->delete();
            Championship::query()->whereIn('id', $championshipIds)->delete();
        }

        Team::query()->whereIn('name', self::DEMO_TEAM_NAMES)->delete();

        TeamInvitation::query()
            ->whereHas('invitedUser', fn ($query) => $query->whereIn('email', [
                'jogador.beatriz@myclub.app',
            ]))
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function loadReferences(): array
    {
        return [
            'sport_modes' => [
                'campo' => SportMode::query()->where('key', 'campo')->firstOrFail(),
                'society' => SportMode::query()->where('key', 'society')->firstOrFail(),
                'quadra' => SportMode::query()->where('key', 'quadra')->firstOrFail(),
                'areia' => SportMode::query()->where('key', 'areia')->firstOrFail(),
            ],
            'category' => Category::query()->where('key', 'livre')->firstOrFail(),
            'positions' => [
                'atacante' => Position::query()->where('key', 'atacante')->firstOrFail(),
                'meia' => Position::query()->where('key', 'meia_ligacao')->firstOrFail(),
                'zagueiro' => Position::query()->where('key', 'zagueiro')->firstOrFail(),
                'goleiro' => Position::query()->where('key', 'goleiro')->firstOrFail(),
                'pivo' => Position::query()->where('key', 'pivo')->firstOrFail(),
            ],
            'staff_roles' => [
                'head_coach' => StaffRole::query()->where('name', 'head_coach')->firstOrFail(),
            ],
            'badge_types' => [
                'golden_ball' => BadgeType::query()->where('name', 'golden_ball')->firstOrFail(),
                'top_scorer' => BadgeType::query()->where('name', 'top_scorer')->firstOrFail(),
                'best_assist' => BadgeType::query()->where('name', 'best_assist')->firstOrFail(),
                'fair_play' => BadgeType::query()->where('name', 'fair_play')->firstOrFail(),
                'hat_trick' => BadgeType::query()
                    ->where('name', 'hat_trick')
                    ->where('scope', BadgeScope::Career->value)
                    ->firstOrFail(),
            ],
        ];
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, mixed>  $refs
     * @return array<string, TeamSportMode>
     */
    private function seedTeams(array $users, array $refs): array
    {
        $lobos = $this->createTeam($users['owner_alpha'], 'Lobos FC');
        $estrela = $this->createTeam($users['owner_beta'], 'Estrela Azul');
        $racha = $this->createTeam($users['hybrid_carlos'], 'Racha Central');
        $cidade = $this->createTeam($users['hybrid_marina'], 'Cidade Nova');

        return [
            'lobos_campo' => $this->syncTeamSportMode($lobos, $refs['sport_modes']['campo']),
            'lobos_society' => $this->syncTeamSportMode($lobos, $refs['sport_modes']['society']),
            'estrela_campo' => $this->syncTeamSportMode($estrela, $refs['sport_modes']['campo']),
            'racha_campo' => $this->syncTeamSportMode($racha, $refs['sport_modes']['campo']),
            'racha_quadra' => $this->syncTeamSportMode($racha, $refs['sport_modes']['quadra']),
            'cidade_campo' => $this->syncTeamSportMode($cidade, $refs['sport_modes']['campo']),
            'cidade_areia' => $this->syncTeamSportMode($cidade, $refs['sport_modes']['areia']),
        ];
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, TeamSportMode>  $teams
     * @param  array<string, mixed>  $refs
     */
    private function seedPlayersAndMemberships(array $users, array $teams, array $refs): void
    {
        $this->upsertPlayer($users['hybrid_carlos'], [
            'city' => 'Manaus',
            'state' => 'AM',
            'country' => 'BR',
            'is_discoverable' => true,
            'history_public' => true,
        ]);

        $this->upsertPlayer($users['hybrid_marina'], [
            'city' => 'Manaus',
            'state' => 'AM',
            'country' => 'BR',
            'is_discoverable' => true,
            'history_public' => true,
        ]);

        $this->upsertPlayer($users['player_lucas'], [
            'city' => 'Manaus',
            'state' => 'AM',
            'country' => 'BR',
            'is_discoverable' => true,
            'history_public' => true,
        ]);

        $this->upsertPlayer($users['player_beatriz'], [
            'city' => 'Manaus',
            'state' => 'AM',
            'country' => 'BR',
            'is_discoverable' => true,
            'history_public' => false,
        ]);

        $this->upsertPlayer($users['support_gabriel'], [
            'city' => 'Manaus',
            'state' => 'AM',
            'country' => 'BR',
            'is_discoverable' => false,
            'history_public' => false,
        ]);

        $this->syncMembership($teams['racha_campo'], $users['hybrid_carlos'], $refs['positions']['atacante'], true);
        $this->syncMembership($teams['cidade_campo'], $users['hybrid_marina'], $refs['positions']['meia'], true);
        $this->syncMembership($teams['lobos_campo'], $users['player_lucas'], $refs['positions']['atacante'], true);
        $this->syncMembership($teams['lobos_society'], $users['player_lucas'], $refs['positions']['pivo'], false);
        $this->syncMembership($teams['estrela_campo'], $users['support_gabriel'], $refs['positions']['zagueiro'], true);
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, TeamSportMode>  $teams
     * @param  array<string, mixed>  $refs
     */
    private function seedStaff(array $users, array $teams, array $refs): void
    {
        $staffMember = StaffMember::query()->updateOrCreate(
            ['user_id' => $users['staff_renato']->id],
            ['staff_role_id' => $refs['staff_roles']['head_coach']->id],
        );

        TeamStaff::query()->firstOrCreate([
            'team_id' => $teams['lobos_campo']->team_id,
            'staff_member_id' => $staffMember->user_id,
        ]);
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, TeamSportMode>  $teams
     * @param  array<string, mixed>  $refs
     */
    private function seedInvitations(array $users, array $teams, array $refs): void
    {
        TeamInvitation::query()->updateOrCreate(
            [
                'team_sport_mode_id' => $teams['estrela_campo']->id,
                'invited_user_id' => $users['player_beatriz']->id,
            ],
            [
                'invited_by' => $users['owner_beta']->id,
                'position_id' => $refs['positions']['meia']->id,
                'status' => InvitationStatus::Pending,
                'expires_at' => now()->addDays(5),
                'message' => 'Queremos voce no elenco para a proxima liga.',
            ],
        );
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, TeamSportMode>  $teams
     * @param  array<string, mixed>  $refs
     */
    private function seedChampionships(array $users, array $teams, array $refs): void
    {
        $draft = $this->createLeague($users['organizer'], 'Liga Demo Draft 2026', $refs['sport_modes']['campo'], $refs['category']);
        $this->createLeagueEnrollment(
            $users['organizer'],
            'Liga Demo Inscricoes 2026',
            $refs['sport_modes']['campo'],
            $refs['category'],
            [$teams['lobos_campo'], $teams['estrela_campo']],
        );

        $this->createLeagueActive(
            $users['organizer'],
            'Liga Demo Ativa 2026',
            $refs['sport_modes']['campo'],
            $refs['category'],
            [$teams['lobos_campo'], $teams['estrela_campo'], $teams['racha_campo']],
        );

        $this->createLeagueFinished(
            $users['organizer'],
            'Liga Demo Finalizada 2025',
            $refs['sport_modes']['campo'],
            $refs['category'],
            [$teams['lobos_campo'], $teams['estrela_campo'], $teams['cidade_campo']],
            $refs['badge_types'],
        );

        $archived = $this->createLeagueFinished(
            $users['admin'],
            'Liga Demo Arquivada 2024',
            $refs['sport_modes']['campo'],
            $refs['category'],
            [$teams['lobos_campo'], $teams['racha_campo'], $teams['cidade_campo']],
            $refs['badge_types'],
        );
        $archived->update([
            'status' => ChampionshipStatus::Archived,
            'updated_at' => now()->subDays(10),
        ]);

        $cancelled = $this->createLeagueEnrollment(
            $users['organizer'],
            'Liga Demo Cancelada 2026',
            $refs['sport_modes']['campo'],
            $refs['category'],
            [$teams['estrela_campo']],
        );
        $cancelled->update(['status' => ChampionshipStatus::Cancelled]);

        $draft->refresh();
    }

    private function createLeague(User $organizer, string $name, SportMode $sportMode, Category $category): Championship
    {
        $championship = Championship::query()->create([
            'created_by' => $organizer->id,
            'name' => $name,
            'description' => 'Campeonato demo para navegacao no frontend.',
            'location' => 'Arena Demo',
            'starts_at' => now()->addWeek()->toDateString(),
            'ends_at' => now()->addWeeks(6)->toDateString(),
            'format' => 'league',
            'status' => ChampionshipStatus::Draft,
            'max_players' => 20,
            'category_id' => $category->id,
        ]);

        $championship->sportModes()->sync([$sportMode->id]);

        return $championship->fresh(['creator', 'category', 'sportModes']);
    }

    /**
     * @param  array<int, TeamSportMode>  $teamSportModes
     */
    private function createLeagueEnrollment(User $organizer, string $name, SportMode $sportMode, Category $category, array $teamSportModes): Championship
    {
        $championship = $this->createLeague($organizer, $name, $sportMode, $category);
        $service = app(ChampionshipService::class);
        $enrollment = app(ChampionshipEnrollmentService::class);

        $championship = $service->openEnrollment($championship);

        foreach ($teamSportModes as $teamSportMode) {
            $enrollment->enroll($championship, $teamSportMode);
            $enrollment->selectPlayers(
                $championship,
                $teamSportMode,
                PlayerMembership::query()
                    ->where('team_sport_mode_id', $teamSportMode->id)
                    ->whereNull('left_at')
                    ->pluck('id')
                    ->all(),
            );
        }

        return $championship->fresh();
    }

    /**
     * @param  array<int, TeamSportMode>  $teamSportModes
     */
    private function createLeagueActive(User $organizer, string $name, SportMode $sportMode, Category $category, array $teamSportModes): Championship
    {
        $championship = $this->createLeagueEnrollment($organizer, $name, $sportMode, $category, $teamSportModes);
        $service = app(ChampionshipService::class);

        $championship = $service->activate($championship);

        $firstMatch = ChampionshipMatch::query()
            ->whereHas('round.phase', fn ($query) => $query->where('championship_id', $championship->id))
            ->orderBy('id')
            ->first();

        if ($firstMatch) {
            $homeMembershipId = PlayerMembership::query()
                ->where('team_sport_mode_id', $firstMatch->home_team_id)
                ->whereNull('left_at')
                ->value('id');

            $awayMembershipId = PlayerMembership::query()
                ->where('team_sport_mode_id', $firstMatch->away_team_id)
                ->whereNull('left_at')
                ->value('id');

            $firstMatch->update([
                'match_status' => MatchStatus::Completed,
                'home_goals' => 2,
                'away_goals' => 1,
                'location' => 'Arena Demo 1',
            ]);

            if ($homeMembershipId) {
                ChampionshipMatchHighlight::query()->updateOrCreate(
                    [
                        'championship_match_id' => $firstMatch->id,
                        'player_membership_id' => $homeMembershipId,
                    ],
                    [
                        'goals' => 2,
                        'assists' => 1,
                        'yellow_cards' => 0,
                        'red_cards' => 0,
                        'is_mvp' => true,
                    ],
                );
            }

            if ($awayMembershipId) {
                ChampionshipMatchHighlight::query()->updateOrCreate(
                    [
                        'championship_match_id' => $firstMatch->id,
                        'player_membership_id' => $awayMembershipId,
                    ],
                    [
                        'goals' => 1,
                        'assists' => 0,
                        'yellow_cards' => 1,
                        'red_cards' => 0,
                        'is_mvp' => false,
                    ],
                );
            }
        }

        return $championship->fresh();
    }

    /**
     * @param  array<int, TeamSportMode>  $teamSportModes
     * @param  array<string, BadgeType>  $badgeTypes
     */
    private function createLeagueFinished(
        User $organizer,
        string $name,
        SportMode $sportMode,
        Category $category,
        array $teamSportModes,
        array $badgeTypes
    ): Championship {
        $championship = $this->createLeagueEnrollment($organizer, $name, $sportMode, $category, $teamSportModes);
        $service = app(ChampionshipService::class);
        $closing = app(ChampionshipClosingService::class);

        $championship = $service->activate($championship);

        $matches = ChampionshipMatch::query()
            ->whereHas('round.phase', fn ($query) => $query->where('championship_id', $championship->id))
            ->orderBy('id')
            ->get();

        foreach ($matches as $index => $match) {
            $homeMembershipId = PlayerMembership::query()
                ->where('team_sport_mode_id', $match->home_team_id)
                ->whereNull('left_at')
                ->value('id');

            $awayMembershipId = PlayerMembership::query()
                ->where('team_sport_mode_id', $match->away_team_id)
                ->whereNull('left_at')
                ->value('id');

            $match->update([
                'match_status' => MatchStatus::Completed,
                'home_goals' => $index === 0 ? 3 : 1,
                'away_goals' => 1,
                'location' => 'Arena Historica',
            ]);

            if ($homeMembershipId) {
                ChampionshipMatchHighlight::query()->updateOrCreate(
                    [
                        'championship_match_id' => $match->id,
                        'player_membership_id' => $homeMembershipId,
                    ],
                    [
                        'goals' => $index === 0 ? 3 : 1,
                        'assists' => 1,
                        'yellow_cards' => 0,
                        'red_cards' => 0,
                        'is_mvp' => true,
                    ],
                );
            }

            if ($awayMembershipId) {
                ChampionshipMatchHighlight::query()->updateOrCreate(
                    [
                        'championship_match_id' => $match->id,
                        'player_membership_id' => $awayMembershipId,
                    ],
                    [
                        'goals' => 1,
                        'assists' => 0,
                        'yellow_cards' => 0,
                        'red_cards' => 0,
                        'is_mvp' => false,
                    ],
                );
            }
        }

        $closing->finish($championship);

        foreach (['golden_ball', 'top_scorer', 'best_assist', 'fair_play', 'hat_trick'] as $badgeName) {
            $badgeType = $badgeTypes[$badgeName];
            $playerId = ChampionshipAward::query()
                ->where('championship_id', $championship->id)
                ->value('player_id');

            if ($playerId && ! PlayerBadge::query()
                ->where('player_id', $playerId)
                ->where('badge_type_id', $badgeType->id)
                ->where('championship_id', $championship->id)
                ->exists()) {
                PlayerBadge::query()->create([
                    'player_id' => $playerId,
                    'badge_type_id' => $badgeType->id,
                    'championship_id' => $championship->id,
                    'awarded_at' => now(),
                    'notes' => "Campeonato: {$championship->name}",
                    'year' => now()->year,
                ]);
            }
        }

        return $championship->fresh();
    }

    private function upsertUser(string $email, string $name, string $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'role' => $role,
                'email_verified_at' => now(),
                'password' => self::PASSWORD,
            ],
        );
    }

    private function createTeam(User $owner, string $name): Team
    {
        return Team::query()->updateOrCreate(
            [
                'owner_id' => $owner->id,
                'name' => $name,
            ],
            [
                'description' => "Time demo {$name}",
                'badge' => null,
                'is_active' => true,
            ],
        );
    }

    private function syncTeamSportMode(Team $team, SportMode $sportMode): TeamSportMode
    {
        return TeamSportMode::query()->firstOrCreate([
            'team_id' => $team->id,
            'sport_mode_id' => $sportMode->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertPlayer(User $user, array $data): Player
    {
        return Player::query()->updateOrCreate(
            ['user_id' => $user->id],
            array_merge([
                'cpf' => null,
                'rg' => null,
                'birth_date' => now()->subYears(24)->toDateString(),
                'phone' => null,
                'is_discoverable' => false,
                'history_public' => false,
                'city' => 'Manaus',
                'state' => 'AM',
                'country' => 'BR',
            ], $data),
        );
    }

    private function syncMembership(TeamSportMode $teamSportMode, User $user, Position $position, bool $isStarter, bool $ensurePlayerProfile = true): PlayerMembership
    {
        if ($ensurePlayerProfile) {
            $this->upsertPlayer($user, []);
        }

        $membership = PlayerMembership::query()
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->where('player_id', $user->id)
            ->whereNull('left_at')
            ->first();

        if ($membership) {
            $membership->update([
                'position_id' => $position->id,
                'is_starter' => $isStarter,
            ]);

            return $membership->fresh();
        }

        return PlayerMembership::query()->create([
            'team_sport_mode_id' => $teamSportMode->id,
            'player_id' => $user->id,
            'position_id' => $position->id,
            'is_starter' => $isStarter,
            'left_at' => null,
        ]);
    }
}
