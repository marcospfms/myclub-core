# Roadmap Fase 6 — Rankings e Cache

> Detalhamento completo de implementação da Fase 6. Cobertura: migrations, models, services, form requests, resources, controllers, rotas, types TypeScript, factories e testes.
>
> **Pré-requisito:** Fase 3 concluída (`championships` e `championship_matches` ativos; `player_badges` e `championship_awards` disponíveis). Fase 4 concluída (`users.plan`, `UserPlan`, `PlanGatingService`).
>
> Referências de schema: `docs/database/schema.md` §3 (Times e Elencos), §4 (Campeonatos), §5 (Partidas e Desempenho).
> Referências de produto: `docs/product/feature-gating.md`, `docs/product/authorization-rules.md`.
> Referências de padrões: `docs/patterns/`.

---

## 1. Objetivo

Implementar o snapshot de estatísticas de times (`team_stats_cache`) e os endpoints de ranking público. O cache é atualizado via write-through sempre que um amistoso é concluído ou um campeonato é encerrado. Rankings são públicos para todos os usuários. Jogadores com plano **Player Pro** recebem destaque visual no card de time quando o time aparece no ranking. Resolve o gap G9 com a adição de `teams.is_active`.

---

## 2. Escopo

| Entrega                                   | Descrição                                                                               |
| ----------------------------------------- | --------------------------------------------------------------------------------------- |
| Migration `add_is_active_to_teams_table`  | Coluna `is_active` em `teams` — soft delete para times abandonados (resolve G9)         |
| Migration `create_team_stats_cache_table` | Tabela de snapshot de estatísticas por `team_sport_mode_id`                             |
| Atualização model `Team`                  | `is_active` em `$fillable`, cast, scope `active()`                                      |
| Model `TeamStatsCache`                    | Snapshot de estatísticas; relacionamento com `TeamSportMode`                            |
| `TeamStatsCacheService`                   | Recalcula e persiste o cache de um time a partir de campeonatos e amistosos concluídos  |
| `RankingService`                          | Consulta `team_stats_cache` com filtros, paginação e ordenação                          |
| Atualização `FriendlyMatchService`        | Chama `TeamStatsCacheService` ao transicionar para `completed`                          |
| Atualização `ChampionshipService`         | Chama `TeamStatsCacheService` ao transicionar para `finished`                           |
| `TeamStatsResource`                       | Serialização do ranking entry — inclui flag `is_pro` para destaque visual               |
| `RankingController`                       | Endpoint público de ranking de times                                                    |
| Rotas API                                 | `GET /api/v1/rankings/teams` (público) · `GET /api/v1/rankings/teams/{teamSportModeId}` |
| Types TypeScript                          | `TeamStatsCache`, `RankingEntry`, `RankingFilters`                                      |
| Factory `TeamStatsCacheFactory`           | Geração de snapshots para testes                                                        |
| Testes Feature (3 classes)                | Cache recalculation, ranking query, destaque Player Pro                                 |

### Progresso atual

⬜ Nenhum bloco desta fase foi iniciado.

---

## 3. Decisões de modelagem

### 3.1 Estratégia de cache: write-through por snapshot de banco

O cache de estatísticas **não usa Laravel Cache** (Redis/Memcached). É uma tabela de banco de dados (`team_stats_cache`) que mantém um snapshot atualizado. A cada evento relevante (amistoso concluído, campeonato encerrado), o service recalcula e sobrescreve o registro do time envolvido.

**Motivação:** evitar deserialização de objetos complexos em memória nesta fase; manter auditabilidade via SQL; simplicidade operacional sem infraestrutura adicional.

### 3.2 O que é incluído no cálculo de estatísticas

O snapshot agrega:

| Métrica               | Origem dos dados                                                                     |
| --------------------- | ------------------------------------------------------------------------------------ |
| `matches_played`      | `friendly_matches` (completed) + `championship_matches` (completed)                  |
| `wins`                | Partidas em que o time marcou mais gols (ou venceu nos pênaltis)                     |
| `draws`               | Partidas empatadas (sem pênaltis decisivos)                                          |
| `losses`              | Partidas em que o time sofreu mais gols (ou perdeu nos pênaltis)                     |
| `goals_for`           | Total de gols marcados (`home_goals` quando casa, `away_goals` quando visitante)     |
| `goals_against`       | Total de gols sofridos                                                               |
| `goal_difference`     | `goals_for - goals_against`                                                          |
| `points`              | `wins * 3 + draws * 1` (considerando apenas amistosos e campeonatos format `league`) |
| `championship_titles` | Contagem de campeonatos encerrados em que o time foi campeão (1º no ranking final)   |

> **Nota sobre pôntos:** partidas de mata-mata (`knockout`) não contam ponto — apenas vitórias/derrotas. O campo `points` reflete apenas resultados de jogos de roundrobin (league e group_stage). Pôntos de mata-mata são irrelevantes para ranking de temporada.

### 3.3 Granularidade: `team_sport_mode_id`

O ranking é por `team_sport_mode_id` (time + modalidade), não pelo time genérico. Um time que joga Campo e Society tem dois registros na `team_stats_cache`. O endpoint suporta filtro por `sport_mode_id`.

### 3.4 Rankings são públicos

Qualquer visitante (autenticado ou não) pode consultar os rankings. Não há guard de plano para leitura.

**Destaque Player Pro:** se o dono do time (`teams.owner_id`) tem plano Player Pro ou superior, o resource retorna `owner_is_pro: true`. O frontend usa esse flag para renderizar o card diferenciado. O backend não discrimina; apenas expõe o dado.

### 3.5 `teams.is_active` — resolve G9

Times com `is_active = false` são excluídos das listagens de busca e dos rankings. Não são deletados — o histórico permanece. O dono pode desativar o próprio time. `admin` pode ativar/desativar qualquer time.

---

## 4. Contexto de Domínio

```
teams
 ├── is_active                            ← novo nesta fase (G9)
 └── team_sport_modes
      └── team_stats_cache                ← novo nesta fase (1:1 com team_sport_modes)
           ├── matches_played
           ├── wins / draws / losses
           ├── goals_for / goals_against / goal_difference
           ├── points
           ├── championship_titles
           └── last_calculated_at

Triggers de recalculate:
  friendly_matches → completed
    → TeamStatsCacheService::recalculate(home_team_id)
    → TeamStatsCacheService::recalculate(away_team_id)

  championships → finished
    → para cada time em championship_teams:
        TeamStatsCacheService::recalculate(team_sport_mode_id)
```

---

## 5. Migrations

### 5.1 `add_is_active_to_teams_table`

```php
Schema::table('teams', function (Blueprint $table) {
    $table->boolean('is_active')->default(true)->after('badge');
});
```

### 5.2 `create_team_stats_cache_table`

```php
Schema::create('team_stats_cache', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_sport_mode_id')
          ->unique()
          ->constrained()
          ->cascadeOnDelete();
    $table->unsignedInteger('matches_played')->default(0);
    $table->unsignedInteger('wins')->default(0);
    $table->unsignedInteger('draws')->default(0);
    $table->unsignedInteger('losses')->default(0);
    $table->unsignedInteger('goals_for')->default(0);
    $table->unsignedInteger('goals_against')->default(0);
    $table->integer('goal_difference')->default(0);
    $table->unsignedInteger('points')->default(0);
    $table->unsignedInteger('championship_titles')->default(0);
    $table->timestamp('last_calculated_at')->nullable();
    $table->timestamps();
});
```

> Unicidade em `team_sport_mode_id` garante exatamente um registro por `(team, sport_mode)`.

---

## 6. Models

### 6.1 `Team` — atualizações

Adicionar à classe `Team` existente:

```php
// Em $fillable — acrescentar:
'is_active',

// Em casts():
'is_active' => 'boolean',

// Scope:
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

### 6.2 `TeamStatsCache` (novo)

Localização: `app/Models/TeamStatsCache.php`

```php
class TeamStatsCache extends Model
{
    protected $table = 'team_stats_cache';

    protected $fillable = [
        'team_sport_mode_id',
        'matches_played',
        'wins',
        'draws',
        'losses',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
        'championship_titles',
        'last_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'last_calculated_at' => 'datetime',
        ];
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }
}
```

---

## 7. Services

Localização: `app/Services/Rankings/`

| Service                 | Arquivo                                           |
| ----------------------- | ------------------------------------------------- |
| `TeamStatsCacheService` | `app/Services/Rankings/TeamStatsCacheService.php` |
| `RankingService`        | `app/Services/Rankings/RankingService.php`        |

### 7.1 `TeamStatsCacheService`

```php
class TeamStatsCacheService
{
    /**
     * Recalcula e persiste o snapshot de estatísticas para um team_sport_mode.
     *
     * Chamado por FriendlyMatchService (on completed) e ChampionshipService (on finished).
     */
    public function recalculate(int $teamSportModeId): TeamStatsCache
    {
        $stats = DB::transaction(function () use ($teamSportModeId) {
            $friendly  = $this->aggregateFriendly($teamSportModeId);
            $champ     = $this->aggregateChampionship($teamSportModeId);
            $titles    = $this->countChampionshipTitles($teamSportModeId);

            $wins            = $friendly['wins']   + $champ['wins'];
            $draws           = $friendly['draws']  + $champ['draws'];
            $losses          = $friendly['losses'] + $champ['losses'];
            $goalsFor        = $friendly['goals_for']     + $champ['goals_for'];
            $goalsAgainst    = $friendly['goals_against'] + $champ['goals_against'];
            $points          = ($wins * 3) + $draws;

            return TeamStatsCache::updateOrCreate(
                ['team_sport_mode_id' => $teamSportModeId],
                [
                    'matches_played'       => $wins + $draws + $losses,
                    'wins'                 => $wins,
                    'draws'                => $draws,
                    'losses'               => $losses,
                    'goals_for'            => $goalsFor,
                    'goals_against'        => $goalsAgainst,
                    'goal_difference'      => $goalsFor - $goalsAgainst,
                    'points'               => $points,
                    'championship_titles'  => $titles,
                    'last_calculated_at'   => now(),
                ]
            );
        });

        return $stats;
    }

    private function aggregateFriendly(int $teamSportModeId): array
    {
        // Soma gols e classifica W/D/L para partidas amistosas concluídas.
        // Uma partida entra duas vezes (home e away) — filtramos por cada papel.
        $rows = FriendlyMatch::query()
            ->where('match_status', 'completed')
            ->where(fn ($q) =>
                $q->where('home_team_id', $teamSportModeId)
                  ->orWhere('away_team_id', $teamSportModeId)
            )
            ->get(['home_team_id', 'away_team_id', 'home_goals', 'away_goals']);

        return $this->tabulateResults($rows, $teamSportModeId, false);
    }

    private function aggregateChampionship(int $teamSportModeId): array
    {
        // Apenas partidas de fases de grupos (league ou group_stage) contam para pontos.
        // Mata-mata conta apenas W/D/L e gols, mas não pontos.
        $rows = ChampionshipMatch::query()
            ->where('match_status', 'completed')
            ->where(fn ($q) =>
                $q->where('home_team_id', $teamSportModeId)
                  ->orWhere('away_team_id', $teamSportModeId)
            )
            ->get(['home_team_id', 'away_team_id', 'home_goals', 'away_goals',
                   'home_penalties', 'away_penalties']);

        return $this->tabulateResults($rows, $teamSportModeId, true);
    }

    /**
     * @param  \Illuminate\Support\Collection  $rows
     * @param  bool  $hasPenalties  indica se as partidas podem ter campo penalties
     */
    private function tabulateResults(
        \Illuminate\Support\Collection $rows,
        int $teamSportModeId,
        bool $hasPenalties
    ): array {
        $wins = $draws = $losses = $goalsFor = $goalsAgainst = 0;

        foreach ($rows as $match) {
            $isHome       = $match->home_team_id === $teamSportModeId;
            $teamGoals    = $isHome ? $match->home_goals    : $match->away_goals;
            $oppGoals     = $isHome ? $match->away_goals    : $match->home_goals;
            $teamPen      = $hasPenalties ? ($isHome ? $match->home_penalties : $match->away_penalties) : null;
            $oppPen       = $hasPenalties ? ($isHome ? $match->away_penalties : $match->home_penalties) : null;

            $goalsFor     += $teamGoals ?? 0;
            $goalsAgainst += $oppGoals  ?? 0;

            if ($teamGoals > $oppGoals) {
                $wins++;
            } elseif ($teamGoals === $oppGoals) {
                // Empate: verificar pênaltis
                if ($hasPenalties && $teamPen !== null && $oppPen !== null) {
                    $teamPen > $oppPen ? $wins++ : $losses++;
                } else {
                    $draws++;
                }
            } else {
                $losses++;
            }
        }

        return [
            'wins'          => $wins,
            'draws'         => $draws,
            'losses'        => $losses,
            'goals_for'     => $goalsFor,
            'goals_against' => $goalsAgainst,
        ];
    }

    private function countChampionshipTitles(int $teamSportModeId): int
    {
        // Um título é contado quando o time está em um campeonato 'finished'
        // e ocupa a posição 1 no grupo final (league) ou venceu a última rodada (knockout/cup).
        // Nesta fase, contamos apenas campeonatos formato 'league' encerrados
        // onde o time lidera o grupo (final_position = 1).
        return ChampionshipGroupEntry::query()
            ->where('team_sport_mode_id', $teamSportModeId)
            ->where('final_position', 1)
            ->whereHas('group.phase.championship', fn ($q) =>
                $q->where('status', 'finished')
                  ->where('format', 'league')
            )
            ->count();
    }
}
```

> **Nota de evolutividade:** em Fase 7 (campeonatos knockout/cup), `countChampionshipTitles` será estendido para incluir vencedores da fase final de mata-mata.

### 7.2 `RankingService`

```php
class RankingService
{
    /**
     * Retorna o ranking de times, com filtros opcionais e paginação.
     *
     * Filtros aceitos:
     *   - sport_mode_id  (obrigatório — rankings são por modalidade)
     *   - category_id    (opcional)
     *
     * Ordenação: points DESC, goal_difference DESC, goals_for DESC
     */
    public function listTeams(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return TeamStatsCache::query()
            ->with([
                'teamSportMode.team.owner',
                'teamSportMode.sportMode',
            ])
            ->whereHas('teamSportMode', function ($q) use ($filters) {
                $q->where('sport_mode_id', $filters['sport_mode_id']);

                // Apenas times ativos aparecem no ranking
                $q->whereHas('team', fn ($t) => $t->where('is_active', true));
            })
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('goals_for')
            ->paginate($perPage);
    }

    /**
     * Retorna o snapshot de um time específico.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByTeamSportMode(int $teamSportModeId): TeamStatsCache
    {
        return TeamStatsCache::with([
            'teamSportMode.team.owner',
            'teamSportMode.sportMode',
        ])
        ->where('team_sport_mode_id', $teamSportModeId)
        ->firstOrFail();
    }
}
```

### 7.3 Atualização `FriendlyMatchService`

Ao encerrar o amistoso (transição para `completed`), adicionar:

```php
// Após persistir match_status = 'completed':
$this->teamStatsCacheService->recalculate($match->home_team_id);
$this->teamStatsCacheService->recalculate($match->away_team_id);
```

O `FriendlyMatchService` recebe `TeamStatsCacheService` via injeção de dependência no construtor.

### 7.4 Atualização `ChampionshipService`

Ao encerrar o campeonato (transição para `finished`), após distribuídos os prêmios e badges:

```php
// Após championship_awards e player_badges gravados:
$teamSportModeIds = $championship->teams()->pluck('team_sport_mode_id');

foreach ($teamSportModeIds as $id) {
    $this->teamStatsCacheService->recalculate($id);
}
```

---

## 8. Form Requests

### 8.1 `TeamRankingRequest`

Localização: `app/Http/Requests/Api/TeamRankingRequest.php`

```php
class TeamRankingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Rankings são públicos
    }

    public function rules(): array
    {
        return [
            'sport_mode_id' => ['required', 'integer', 'exists:sport_modes,id'],
        ];
    }
}
```

---

## 9. Resource

### 9.1 `TeamStatsResource`

Localização: `app/Http/Resources/TeamStatsResource.php`

```php
class TeamStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $team     = $this->teamSportMode->team;
        $owner    = $team->owner;
        $isPro    = $owner && $owner->isPlanAtLeast(UserPlan::PlayerPro);

        return [
            'team_sport_mode_id'    => $this->team_sport_mode_id,
            'team_id'               => $team->id,
            'team_name'             => $team->name,
            'team_badge'            => $team->badge,
            'sport_mode'            => $this->whenLoaded('teamSportMode', fn () =>
                $this->teamSportMode->sportMode->name
            ),
            'owner_is_pro'          => $isPro, // flag para destaque visual no frontend
            'matches_played'        => $this->matches_played,
            'wins'                  => $this->wins,
            'draws'                 => $this->draws,
            'losses'                => $this->losses,
            'goals_for'             => $this->goals_for,
            'goals_against'         => $this->goals_against,
            'goal_difference'       => $this->goal_difference,
            'points'                => $this->points,
            'championship_titles'   => $this->championship_titles,
            'last_calculated_at'    => $this->last_calculated_at,
        ];
    }
}
```

---

## 10. Controller

### 10.1 `RankingController`

Localização: `app/Http/Controllers/Api/RankingController.php`

```php
class RankingController extends BaseController
{
    public function __construct(
        private RankingService $rankingService,
    ) {
    }

    /**
     * GET /api/v1/rankings/teams?sport_mode_id=1
     *
     * Listagem pública do ranking de times por modalidade.
     */
    public function teams(TeamRankingRequest $request): JsonResponse
    {
        $ranking = $this->rankingService->listTeams($request->validated());

        return $this->sendResponse(
            TeamStatsResource::collection($ranking),
            'Rankings retrieved successfully.'
        );
    }

    /**
     * GET /api/v1/rankings/teams/{teamSportModeId}
     *
     * Snapshot de um time específico no ranking.
     */
    public function show(int $teamSportModeId): JsonResponse
    {
        $stats = $this->rankingService->findByTeamSportMode($teamSportModeId);

        return $this->sendResponse(
            new TeamStatsResource($stats),
            'Team stats retrieved successfully.'
        );
    }
}
```

---

## 11. Rotas

Adicionar em `routes/api.php`:

```php
// Rankings — públicos (sem auth:sanctum)
Route::prefix('v1/rankings')->group(function () {
    Route::get('teams', [RankingController::class, 'teams']);
    Route::get('teams/{teamSportModeId}', [RankingController::class, 'show'])
         ->whereNumber('teamSportModeId');
});
```

---

## 12. Types TypeScript

Localização: `resources/js/types/ranking.ts`

```typescript
export interface TeamStatsCache {
    team_sport_mode_id: number;
    team_id: number;
    team_name: string;
    team_badge: string | null;
    sport_mode: string;
    owner_is_pro: boolean;
    matches_played: number;
    wins: number;
    draws: number;
    losses: number;
    goals_for: number;
    goals_against: number;
    goal_difference: number;
    points: number;
    championship_titles: number;
    last_calculated_at: string | null;
}

export type RankingEntry = TeamStatsCache;

export interface RankingFilters {
    sport_mode_id: number;
}
```

---

## 13. Factory

### 13.1 `TeamStatsCacheFactory`

Localização: `database/factories/TeamStatsCacheFactory.php`

```php
class TeamStatsCacheFactory extends Factory
{
    protected $model = TeamStatsCache::class;

    public function definition(): array
    {
        $wins   = fake()->numberBetween(0, 20);
        $draws  = fake()->numberBetween(0, 10);
        $losses = fake()->numberBetween(0, 15);
        $gf     = fake()->numberBetween(0, 60);
        $ga     = fake()->numberBetween(0, 60);

        return [
            'team_sport_mode_id'  => TeamSportMode::factory(),
            'matches_played'      => $wins + $draws + $losses,
            'wins'                => $wins,
            'draws'               => $draws,
            'losses'              => $losses,
            'goals_for'           => $gf,
            'goals_against'       => $ga,
            'goal_difference'     => $gf - $ga,
            'points'              => ($wins * 3) + $draws,
            'championship_titles' => fake()->numberBetween(0, 3),
            'last_calculated_at'  => now(),
        ];
    }

    public function withTitle(): static
    {
        return $this->state(['championship_titles' => fake()->numberBetween(1, 5)]);
    }
}
```

---

## 14. Testes

### 14.1 `RankingTest`

Localização: `tests/Feature/Api/RankingTest.php`

```php
class RankingTest extends TestCase
{
    use RefreshDatabase;

    // ── Happy path ──────────────────────────────────────────────────

    public function test_anyone_can_list_rankings_without_auth(): void
    {
        $sportMode   = SportMode::factory()->create();
        $teamSportMode = TeamSportMode::factory()
            ->for(Team::factory()->create(['is_active' => true]), 'team')
            ->create(['sport_mode_id' => $sportMode->id]);
        TeamStatsCache::factory()->create(['team_sport_mode_id' => $teamSportMode->id]);

        $response = $this->getJson("/api/v1/rankings/teams?sport_mode_id={$sportMode->id}");

        $response->assertOk()
                 ->assertJsonPath('success', true)
                 ->assertJsonCount(1, 'data.data');
    }

    public function test_ranking_is_ordered_by_points_desc(): void
    {
        $sportMode = SportMode::factory()->create();

        // time A: 9 pts; time B: 3 pts
        $tsm1 = TeamSportMode::factory()
            ->for(Team::factory()->create(['is_active' => true]))
            ->create(['sport_mode_id' => $sportMode->id]);
        TeamStatsCache::factory()->create([
            'team_sport_mode_id' => $tsm1->id,
            'points' => 9,
        ]);

        $tsm2 = TeamSportMode::factory()
            ->for(Team::factory()->create(['is_active' => true]))
            ->create(['sport_mode_id' => $sportMode->id]);
        TeamStatsCache::factory()->create([
            'team_sport_mode_id' => $tsm2->id,
            'points' => 3,
        ]);

        $response = $this->getJson("/api/v1/rankings/teams?sport_mode_id={$sportMode->id}");

        $response->assertOk();
        $this->assertEquals(9, $response->json('data.data.0.points'));
        $this->assertEquals(3, $response->json('data.data.1.points'));
    }

    public function test_inactive_teams_are_excluded_from_ranking(): void
    {
        $sportMode = SportMode::factory()->create();

        $tsm = TeamSportMode::factory()
            ->for(Team::factory()->create(['is_active' => false]))
            ->create(['sport_mode_id' => $sportMode->id]);
        TeamStatsCache::factory()->create(['team_sport_mode_id' => $tsm->id]);

        $response = $this->getJson("/api/v1/rankings/teams?sport_mode_id={$sportMode->id}");

        $response->assertOk()
                 ->assertJsonCount(0, 'data.data');
    }

    public function test_owner_is_pro_flag_is_true_for_player_pro_owner(): void
    {
        $proOwner  = User::factory()->create(['plan' => UserPlan::PlayerPro]);
        $sportMode = SportMode::factory()->create();
        $tsm       = TeamSportMode::factory()
            ->for(Team::factory()->for($proOwner, 'owner')->create(['is_active' => true]))
            ->create(['sport_mode_id' => $sportMode->id]);
        TeamStatsCache::factory()->create(['team_sport_mode_id' => $tsm->id]);

        $response = $this->getJson("/api/v1/rankings/teams?sport_mode_id={$sportMode->id}");

        $response->assertOk()
                 ->assertJsonPath('data.data.0.owner_is_pro', true);
    }

    public function test_show_returns_single_team_stats(): void
    {
        $tsm   = TeamSportMode::factory()
            ->for(Team::factory()->create(['is_active' => true]))
            ->create();
        $cache = TeamStatsCache::factory()->create(['team_sport_mode_id' => $tsm->id]);

        $response = $this->getJson("/api/v1/rankings/teams/{$tsm->id}");

        $response->assertOk()
                 ->assertJsonPath('data.team_sport_mode_id', $cache->team_sport_mode_id);
    }

    // ── Validação ────────────────────────────────────────────────────

    public function test_sport_mode_id_is_required(): void
    {
        $this->getJson('/api/v1/rankings/teams')
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['sport_mode_id']);
    }

    public function test_sport_mode_id_must_exist(): void
    {
        $this->getJson('/api/v1/rankings/teams?sport_mode_id=9999')
             ->assertUnprocessable()
             ->assertJsonValidationErrors(['sport_mode_id']);
    }
}
```

### 14.2 `TeamStatsCacheServiceTest`

Localização: `tests/Feature/Api/TeamStatsCacheServiceTest.php`

```php
class TeamStatsCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recalculate_creates_cache_from_friendly_matches(): void
    {
        $tsm = TeamSportMode::factory()->for(Team::factory())->create();

        // time como mandante: 2-1 (vitória)
        FriendlyMatch::factory()->create([
            'home_team_id'  => $tsm->id,
            'away_team_id'  => TeamSportMode::factory()->create()->id,
            'match_status'  => 'completed',
            'home_goals'    => 2,
            'away_goals'    => 1,
        ]);

        $service = app(TeamStatsCacheService::class);
        $cache   = $service->recalculate($tsm->id);

        $this->assertEquals(1, $cache->wins);
        $this->assertEquals(0, $cache->draws);
        $this->assertEquals(0, $cache->losses);
        $this->assertEquals(2, $cache->goals_for);
        $this->assertEquals(1, $cache->goals_against);
        $this->assertEquals(3, $cache->points);
    }

    public function test_recalculate_updates_existing_cache(): void
    {
        $tsm = TeamSportMode::factory()->for(Team::factory())->create();
        TeamStatsCache::factory()->create([
            'team_sport_mode_id' => $tsm->id,
            'wins' => 5, 'points' => 15,
        ]);

        $service = app(TeamStatsCacheService::class);
        $cache   = $service->recalculate($tsm->id); // sem partidas novas

        // Com zero partidas recalculadas, stats devem zerar
        $this->assertEquals(0, $cache->wins);
        $this->assertEquals(0, $cache->points);
    }

    public function test_recalculate_excludes_non_completed_matches(): void
    {
        $tsm = TeamSportMode::factory()->for(Team::factory())->create();

        FriendlyMatch::factory()->create([
            'home_team_id' => $tsm->id,
            'away_team_id' => TeamSportMode::factory()->create()->id,
            'match_status' => 'scheduled', // não concluída
            'home_goals'   => 3,
            'away_goals'   => 0,
        ]);

        $cache = app(TeamStatsCacheService::class)->recalculate($tsm->id);

        $this->assertEquals(0, $cache->matches_played);
    }
}
```

### 14.3 `TeamIsActiveTest`

Localização: `tests/Feature/Api/TeamIsActiveTest.php`

```php
class TeamIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_defaults_to_active_on_creation(): void
    {
        $team = Team::factory()->create();
        $this->assertTrue($team->is_active);
    }

    public function test_owner_can_deactivate_team(): void
    {
        $owner = User::factory()->create();
        $team  = Team::factory()->for($owner, 'owner')->create(['is_active' => true]);

        $response = $this->actingAs($owner)
                         ->patchJson("/api/v1/teams/{$team->id}", ['is_active' => false]);

        $response->assertOk();
        $this->assertFalse($team->fresh()->is_active);
    }

    public function test_non_owner_cannot_deactivate_team(): void
    {
        $other = User::factory()->create();
        $team  = Team::factory()->create(['is_active' => true]);

        $this->actingAs($other)
             ->patchJson("/api/v1/teams/{$team->id}", ['is_active' => false])
             ->assertForbidden();
    }
}
```

---

## 15. Diagrama de Componentes

```
RankingController (API)
  ├── GET /api/v1/rankings/teams          → RankingService::listTeams()
  └── GET /api/v1/rankings/teams/{id}     → RankingService::findByTeamSportMode()
          └── TeamStatsResource (serializa team_stats_cache + owner_is_pro flag)

TeamStatsCacheService (disparado por eventos)
  ├── FriendlyMatchService (on completed) → recalculate(home_id) + recalculate(away_id)
  └── ChampionshipService  (on finished)  → recalculate(all enrolled teams)
          ├── aggregateFriendly() → friendly_matches (completed)
          ├── aggregateChampionship() → championship_matches (completed)
          └── countChampionshipTitles() → championship_group_entries (final_position=1, league)
```
