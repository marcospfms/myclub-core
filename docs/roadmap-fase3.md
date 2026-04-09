# Roadmap Fase 3 — Campeonatos (formato `league`)

> Detalhamento completo de implementação da Fase 3. Cobertura: migrations, models, enums, services, job, form requests, resources, policy, controllers, rotas, types TypeScript e testes.
>
> **Pré‑requisito:** Fases 0, 1 e 2 concluídas (`team_sport_modes`, `player_memberships`, `badge_types` disponíveis).
>
> **Escopo de formato:** apenas `league` (pontos corridos). Formatos `knockout` e `cup` → Fase 7.
>
> Referências de schema: `docs/database/schema.md` §4 e §6.
> Referências de produto: `docs/product/championship-lifecycle.md`, `docs/product/authorization-rules.md`, `docs/product/feature-gating.md`.
> Referências de padrões: `docs/patterns/`.

---

## 1. Escopo

| Item                                                                             | Status      |
| -------------------------------------------------------------------------------- | ----------- |
| Migrations — `championships`, `championship_sport_modes`                         | ⬜ Pendente |
| Migrations — `championship_phases`, `championship_groups`, `championship_rounds` | ⬜ Pendente |
| Migrations — `championship_teams`, `championship_group_entries`                  | ⬜ Pendente |
| Migrations — `championship_team_players`                                         | ⬜ Pendente |
| Migrations — `championship_matches`, `championship_match_highlights`             | ⬜ Pendente |
| Migrations — `championship_awards`, `player_badges`                              | ⬜ Pendente |
| Enums — `ChampionshipStatus`, `ChampionshipFormat`, `PhaseType`, `AwardType`     | ⬜ Pendente |
| Models (11)                                                                      | ⬜ Pendente |
| Services — `ChampionshipService`, `ChampionshipEnrollmentService`                | ⬜ Pendente |
| Services — `ChampionshipMatchService`, `ChampionshipClosingService`              | ⬜ Pendente |
| Job — `ArchiveFinishedChampionships`                                             | ⬜ Pendente |
| Form Requests (6)                                                                | ⬜ Pendente |
| API Resources (5)                                                                | ⬜ Pendente |
| Policy — `ChampionshipPolicy`                                                    | ⬜ Pendente |
| Controllers (5)                                                                  | ⬜ Pendente |
| Rotas API (`routes/api.php`)                                                     | ⬜ Pendente |
| Types TypeScript                                                                 | ⬜ Pendente |
| Factories (5)                                                                    | ⬜ Pendente |
| Testes Feature (5 classes)                                                       | ⬜ Pendente |

---

## 2. Contexto de Domínio

```
users (Fase 0)
 └── championships  (created_by → users)
      ├── championship_sport_modes  → modalidades do campeonato
      ├── championship_phases       → fases (1 fase para league)
      │    ├── championship_groups  → grupos (1 grupo para league)
      │    │    └── championship_group_entries → times do grupo + final_position
      │    └── championship_rounds  → rodadas geradas automaticamente
      │         └── championship_matches → partidas
      │              └── championship_match_highlights → estatísticas individuais
      ├── championship_teams        → times inscritos
      │    └── championship_team_players → jogadores selecionados por time
      └── championship_awards       → prêmios ao encerrar
           └── player_badges        → badges concedidos aos jogadores
```

### Lifecycle do campeonato (formato `league`)

```
draft
  ↓ organizador abre inscrições
enrollment
  ↓ organizador ativa (mín. 3 times inscritos → gera rounds + matches automaticamente)
active
  ↓ sistema detecta todas as partidas completed/cancelled   OU   organizador força encerramento
finished  →  championship_awards calculados  →  player_badges concedidos
  ↓ job automático após 7 dias
archived
```

Cancelamento disponível em `draft`, `enrollment` (pelo organizador) e `active` (somente `admin`).

### Geração de rodadas (round-robin)

Ao transitar para `active`, o sistema gera automaticamente fases, grupo único, rodadas e partidas:

- **N teams pares** → N−1 rodadas, N/2 partidas por rodada
- **N teams ímpares** → N rodadas, (N−1)/2 partidas por rodada (um time descansa por rodada)
- Algoritmo: _circle method_ — um time fixo, os demais rotacionam

---

## 3. Migrations

Ordem de criação obrigatória (respeitar dependências de FK):

### 3.1 `create_championships_table`

```php
Schema::create('championships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name', 45);
    $table->string('description', 255)->nullable();
    $table->string('location', 150)->nullable();
    $table->date('starts_at')->nullable();
    $table->date('ends_at')->nullable();
    $table->enum('format', ['league', 'knockout', 'cup'])->default('league');
    $table->enum('status', ['draft', 'enrollment', 'active', 'finished', 'archived', 'cancelled'])
          ->default('draft');
    $table->integer('max_players')->default(20);
    $table->timestamps();
});
```

### 3.2 `create_championship_sport_modes_table` (pivô)

```php
Schema::create('championship_sport_modes', function (Blueprint $table) {
    $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sport_mode_id')->constrained()->restrictOnDelete();
    $table->primary(['championship_id', 'sport_mode_id']);
    $table->timestamps();
});
```

### 3.3 `create_championship_phases_table`

```php
Schema::create('championship_phases', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
    $table->string('name', 60);
    $table->enum('type', ['group_stage', 'knockout'])->default('group_stage');
    $table->integer('phase_order')->default(1);
    $table->integer('legs')->default(1);
    $table->integer('advances_count')->default(0);
    $table->timestamps();
});
```

### 3.4 `create_championship_groups_table`

```php
Schema::create('championship_groups', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_phase_id')->constrained()->cascadeOnDelete();
    $table->string('name', 10);
    $table->timestamps();
});
```

### 3.5 `create_championship_rounds_table`

```php
Schema::create('championship_rounds', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_phase_id')->constrained()->cascadeOnDelete();
    $table->string('name', 60);
    $table->integer('round_number');
    $table->timestamps();
});
```

### 3.6 `create_championship_teams_table` (pivô)

```php
Schema::create('championship_teams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
    $table->foreignId('team_sport_mode_id')->constrained()->restrictOnDelete();
    $table->unique(['championship_id', 'team_sport_mode_id']);
    $table->timestamps();
});
```

### 3.7 `create_championship_group_entries_table`

```php
Schema::create('championship_group_entries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_group_id')->constrained()->cascadeOnDelete();
    $table->foreignId('team_sport_mode_id')->constrained()->restrictOnDelete();
    $table->integer('final_position')->nullable();
    $table->unique(['championship_group_id', 'team_sport_mode_id']);
    $table->timestamps();
});
```

### 3.8 `create_championship_team_players_table`

```php
Schema::create('championship_team_players', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
    $table->foreignId('team_sport_mode_id')->constrained()->restrictOnDelete();
    $table->foreignId('player_membership_id')->constrained()->restrictOnDelete();
    $table->unique(['championship_id', 'team_sport_mode_id', 'player_membership_id'], 'ctp_unique');
    $table->timestamps();
});
```

### 3.9 `create_championship_matches_table`

```php
Schema::create('championship_matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_round_id')->constrained()->cascadeOnDelete();
    $table->foreignId('home_team_id')->constrained('team_sport_modes')->restrictOnDelete();
    $table->foreignId('away_team_id')->constrained('team_sport_modes')->restrictOnDelete();
    $table->timestamp('scheduled_at')->nullable();
    $table->string('location', 255)->nullable();
    $table->enum('match_status', ['scheduled', 'completed', 'cancelled', 'postponed'])
          ->default('scheduled');
    $table->integer('home_goals')->nullable();
    $table->integer('away_goals')->nullable();
    $table->integer('home_penalties')->nullable();
    $table->integer('away_penalties')->nullable();
    $table->integer('leg')->default(1);
    $table->timestamps();
});
```

### 3.10 `create_championship_match_highlights_table`

```php
Schema::create('championship_match_highlights', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_match_id')->constrained()->cascadeOnDelete();
    $table->foreignId('player_membership_id')->constrained()->restrictOnDelete();
    $table->integer('goals')->default(0);
    $table->integer('assists')->default(0);
    $table->integer('yellow_cards')->default(0);
    $table->integer('red_cards')->default(0);
    $table->boolean('is_mvp')->default(false);
    $table->unique(['championship_match_id', 'player_membership_id'], 'cmh_unique');
    $table->timestamps();
});
```

> `is_mvp = true` deve ser único por `championship_match_id` — validado na camada de service.

### 3.11 `create_championship_awards_table`

```php
Schema::create('championship_awards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('championship_id')->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('player_id');
    $table->foreign('player_id')->references('user_id')->on('players')->restrictOnDelete();
    $table->enum('award_type', ['golden_ball', 'top_scorer', 'best_assist', 'best_goalkeeper', 'fair_play']);
    $table->integer('value')->nullable();
    $table->unique(['championship_id', 'award_type']);
    $table->timestamps();
});
```

### 3.12 `create_player_badges_table`

```php
Schema::create('player_badges', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('player_id');
    $table->foreign('player_id')->references('user_id')->on('players')->cascadeOnDelete();
    $table->foreignId('badge_type_id')->constrained()->restrictOnDelete();
    $table->foreignId('championship_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamp('awarded_at');
    $table->string('notes', 255)->nullable();
    $table->integer('year')->nullable();
    $table->timestamps();
});
```

> `scope = seasonal` exige `year` preenchido — validado na camada de service.
> `badge_types` já existe (Fase 0).

---

## 4. Enums

### `app/Enums/ChampionshipStatus.php`

```php
enum ChampionshipStatus: string
{
    case Draft      = 'draft';
    case Enrollment = 'enrollment';
    case Active     = 'active';
    case Finished   = 'finished';
    case Archived   = 'archived';
    case Cancelled  = 'cancelled';
}
```

### `app/Enums/ChampionshipFormat.php`

```php
enum ChampionshipFormat: string
{
    case League   = 'league';
    case Knockout = 'knockout';
    case Cup      = 'cup';
}
```

### `app/Enums/PhaseType.php`

```php
enum PhaseType: string
{
    case GroupStage = 'group_stage';
    case Knockout   = 'knockout';
}
```

### `app/Enums/AwardType.php`

```php
enum AwardType: string
{
    case GoldenBall     = 'golden_ball';
    case TopScorer      = 'top_scorer';
    case BestAssist     = 'best_assist';
    case BestGoalkeeper = 'best_goalkeeper';
    case FairPlay       = 'fair_play';
}
```

> `BadgeScope` já existe em `app/Enums/BadgeScope.php` (Fase 0). `MatchStatus` já existe (Fase 2).

---

## 5. Models

### 5.1 `Championship`

```php
class Championship extends Model
{
    protected $table = 'championships';

    protected $fillable = [
        'created_by', 'category_id', 'name', 'description',
        'location', 'starts_at', 'ends_at', 'format', 'status', 'max_players',
    ];

    protected function casts(): array
    {
        return [
            'format'    => ChampionshipFormat::class,
            'status'    => ChampionshipStatus::class,
            'starts_at' => 'date',
            'ends_at'   => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'championship_sport_modes')
                    ->withTimestamps();
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ChampionshipPhase::class)->orderBy('phase_order');
    }

    public function teams(): HasMany
    {
        return $this->hasMany(ChampionshipTeam::class);
    }

    public function awards(): HasMany
    {
        return $this->hasMany(ChampionshipAward::class);
    }

    public function isDraft(): bool      { return $this->status === ChampionshipStatus::Draft; }
    public function isEnrollment(): bool { return $this->status === ChampionshipStatus::Enrollment; }
    public function isActive(): bool     { return $this->status === ChampionshipStatus::Active; }
    public function isFinished(): bool   { return $this->status === ChampionshipStatus::Finished; }
}
```

### 5.2 `ChampionshipPhase`

```php
class ChampionshipPhase extends Model
{
    protected $table = 'championship_phases';

    protected $fillable = [
        'championship_id', 'name', 'type', 'phase_order', 'legs', 'advances_count',
    ];

    protected function casts(): array
    {
        return ['type' => PhaseType::class];
    }

    public function championship(): BelongsTo { return $this->belongsTo(Championship::class); }
    public function groups(): HasMany         { return $this->hasMany(ChampionshipGroup::class); }
    public function rounds(): HasMany         { return $this->hasMany(ChampionshipRound::class)->orderBy('round_number'); }
}
```

### 5.3 `ChampionshipGroup`

```php
class ChampionshipGroup extends Model
{
    protected $table = 'championship_groups';
    protected $fillable = ['championship_phase_id', 'name'];

    public function phase(): BelongsTo   { return $this->belongsTo(ChampionshipPhase::class, 'championship_phase_id'); }
    public function entries(): HasMany   { return $this->hasMany(ChampionshipGroupEntry::class); }
}
```

### 5.4 `ChampionshipGroupEntry`

```php
class ChampionshipGroupEntry extends Model
{
    protected $table = 'championship_group_entries';
    protected $fillable = ['championship_group_id', 'team_sport_mode_id', 'final_position'];

    public function group(): BelongsTo       { return $this->belongsTo(ChampionshipGroup::class, 'championship_group_id'); }
    public function teamSportMode(): BelongsTo { return $this->belongsTo(TeamSportMode::class); }
}
```

### 5.5 `ChampionshipRound`

```php
class ChampionshipRound extends Model
{
    protected $table = 'championship_rounds';
    protected $fillable = ['championship_phase_id', 'name', 'round_number'];

    public function phase(): HasMany    { return $this->belongsTo(ChampionshipPhase::class, 'championship_phase_id'); }
    public function matches(): HasMany  { return $this->hasMany(ChampionshipMatch::class); }
}
```

### 5.6 `ChampionshipTeam` (pivot com surrogate PK)

```php
class ChampionshipTeam extends Model
{
    protected $table = 'championship_teams';
    protected $fillable = ['championship_id', 'team_sport_mode_id'];

    public function championship(): BelongsTo  { return $this->belongsTo(Championship::class); }
    public function teamSportMode(): BelongsTo { return $this->belongsTo(TeamSportMode::class); }

    public function players(): HasMany
    {
        return $this->hasMany(ChampionshipTeamPlayer::class, 'team_sport_mode_id', 'team_sport_mode_id')
                    ->where('championship_id', $this->championship_id);
    }
}
```

### 5.7 `ChampionshipTeamPlayer`

```php
class ChampionshipTeamPlayer extends Model
{
    protected $table = 'championship_team_players';
    protected $fillable = ['championship_id', 'team_sport_mode_id', 'player_membership_id'];

    public function membership(): BelongsTo { return $this->belongsTo(PlayerMembership::class, 'player_membership_id'); }
}
```

### 5.8 `ChampionshipMatch`

```php
class ChampionshipMatch extends Model
{
    protected $table = 'championship_matches';

    protected $fillable = [
        'championship_round_id', 'home_team_id', 'away_team_id',
        'scheduled_at', 'location', 'match_status',
        'home_goals', 'away_goals', 'home_penalties', 'away_penalties', 'leg',
    ];

    protected function casts(): array
    {
        return [
            'match_status' => MatchStatus::class,
            'scheduled_at' => 'datetime',
        ];
    }

    public function round(): BelongsTo      { return $this->belongsTo(ChampionshipRound::class, 'championship_round_id'); }
    public function homeTeam(): BelongsTo   { return $this->belongsTo(TeamSportMode::class, 'home_team_id'); }
    public function awayTeam(): BelongsTo   { return $this->belongsTo(TeamSportMode::class, 'away_team_id'); }
    public function highlights(): HasMany   { return $this->hasMany(ChampionshipMatchHighlight::class); }

    public function isCompleted(): bool { return $this->match_status === MatchStatus::Completed; }
}
```

### 5.9 `ChampionshipMatchHighlight`

```php
class ChampionshipMatchHighlight extends Model
{
    protected $table = 'championship_match_highlights';

    protected $fillable = [
        'championship_match_id', 'player_membership_id',
        'goals', 'assists', 'yellow_cards', 'red_cards', 'is_mvp',
    ];

    protected function casts(): array
    {
        return [
            'goals' => 'integer', 'assists' => 'integer',
            'yellow_cards' => 'integer', 'red_cards' => 'integer',
            'is_mvp' => 'boolean',
        ];
    }

    public function match(): BelongsTo           { return $this->belongsTo(ChampionshipMatch::class, 'championship_match_id'); }
    public function playerMembership(): BelongsTo { return $this->belongsTo(PlayerMembership::class); }
}
```

### 5.10 `ChampionshipAward`

```php
class ChampionshipAward extends Model
{
    protected $table = 'championship_awards';
    protected $fillable = ['championship_id', 'player_id', 'award_type', 'value'];

    protected function casts(): array
    {
        return ['award_type' => AwardType::class];
    }

    public function championship(): BelongsTo { return $this->belongsTo(Championship::class); }
    public function player(): BelongsTo       { return $this->belongsTo(Player::class, 'player_id', 'user_id'); }
}
```

### 5.11 `PlayerBadge`

```php
class PlayerBadge extends Model
{
    protected $table = 'player_badges';

    protected $fillable = [
        'player_id', 'badge_type_id', 'championship_id', 'awarded_at', 'notes', 'year',
    ];

    protected function casts(): array
    {
        return ['awarded_at' => 'datetime'];
    }

    public function player(): BelongsTo      { return $this->belongsTo(Player::class, 'player_id', 'user_id'); }
    public function badgeType(): BelongsTo   { return $this->belongsTo(BadgeType::class); }
    public function championship(): BelongsTo { return $this->belongsTo(Championship::class)->withDefault(); }
}
```

---

## 6. Services

Localização: `app/Services/Championship/`

| Service                         | Arquivo                                                       |
| ------------------------------- | ------------------------------------------------------------- |
| `ChampionshipService`           | `app/Services/Championship/ChampionshipService.php`           |
| `ChampionshipEnrollmentService` | `app/Services/Championship/ChampionshipEnrollmentService.php` |
| `ChampionshipMatchService`      | `app/Services/Championship/ChampionshipMatchService.php`      |
| `ChampionshipClosingService`    | `app/Services/Championship/ChampionshipClosingService.php`    |

### 6.1 `ChampionshipService`

```php
class ChampionshipService
{
    public function create(array $data, User $creator): Championship
    {
        return DB::transaction(function () use ($data, $creator) {
            $championship = Championship::create(array_merge($data, [
                'created_by' => $creator->id,
                'status'     => ChampionshipStatus::Draft,
            ]));

            if (!empty($data['sport_mode_ids'])) {
                $championship->sportModes()->sync($data['sport_mode_ids']);
            }

            return $championship->load('sportModes', 'category');
        });
    }

    public function update(Championship $championship, array $data): Championship
    {
        if (!$championship->isDraft()) {
            throw new \DomainException('Apenas campeonatos em rascunho podem ser editados livremente.');
        }

        $championship->update($data);

        if (isset($data['sport_mode_ids'])) {
            $championship->sportModes()->sync($data['sport_mode_ids']);
        }

        return $championship->fresh();
    }

    public function openEnrollment(Championship $championship): Championship
    {
        if (!$championship->isDraft()) {
            throw new \DomainException('Apenas campeonatos em rascunho podem abrir inscrições.');
        }

        if ($championship->sportModes()->doesntExist()) {
            throw new \DomainException('Configure ao menos uma modalidade antes de abrir inscrições.');
        }

        $championship->update(['status' => ChampionshipStatus::Enrollment]);

        return $championship->fresh();
    }

    public function activate(Championship $championship): Championship
    {
        if (!$championship->isEnrollment()) {
            throw new \DomainException('Apenas campeonatos em inscrição podem ser ativados.');
        }

        $teamCount = $championship->teams()->count();

        if ($teamCount < 3) {
            throw new \DomainException('São necessários ao menos 3 times inscritos para iniciar o campeonato.');
        }

        return DB::transaction(function () use ($championship, $teamCount) {
            $championship->update(['status' => ChampionshipStatus::Active]);

            // Cria fase única (group_stage)
            $phase = $championship->phases()->create([
                'name'            => 'Fase Principal',
                'type'            => PhaseType::GroupStage,
                'phase_order'     => 1,
                'legs'            => 1,
                'advances_count'  => 0,
            ]);

            // Cria grupo único contendo todos os times
            $group = $phase->groups()->create(['name' => 'Geral']);

            $teams = $championship->teams()->with('teamSportMode')->get();

            foreach ($teams as $ct) {
                $group->entries()->create(['team_sport_mode_id' => $ct->team_sport_mode_id]);
            }

            // Gera rounds e matches — round-robin (circle method)
            $this->generateLeagueRounds($phase, $teams->pluck('team_sport_mode_id')->toArray());

            return $championship->fresh();
        });
    }

    public function cancel(Championship $championship): Championship
    {
        if ($championship->isFinished() || $championship->status === ChampionshipStatus::Archived) {
            throw new \DomainException('Campeonatos encerrados ou arquivados não podem ser cancelados.');
        }

        $championship->update(['status' => ChampionshipStatus::Cancelled]);

        return $championship->fresh();
    }

    private function generateLeagueRounds(ChampionshipPhase $phase, array $teamIds): void
    {
        $n = count($teamIds);
        $hasGhost = $n % 2 !== 0;

        if ($hasGhost) {
            $teamIds[] = null; // time fantasma (folga)
            $n++;
        }

        $rounds = $n - 1;

        for ($round = 1; $round <= $rounds; $round++) {
            $dbRound = $phase->rounds()->create([
                'name'         => "Rodada {$round}",
                'round_number' => $round,
            ]);

            for ($match = 0; $match < $n / 2; $match++) {
                $home = $teamIds[$match];
                $away = $teamIds[$n - 1 - $match];

                if ($home !== null && $away !== null) {
                    $dbRound->matches()->create([
                        'home_team_id' => $home,
                        'away_team_id' => $away,
                        'match_status' => MatchStatus::Scheduled,
                        'leg'          => 1,
                    ]);
                }
            }

            // Rotaciona — mantém o primeiro fixo
            $fixed = array_shift($teamIds);
            array_unshift($teamIds, array_pop($teamIds), $fixed);
        }
    }
}
```

### 6.2 `ChampionshipEnrollmentService`

```php
class ChampionshipEnrollmentService
{
    public function enroll(Championship $championship, TeamSportMode $tsm): ChampionshipTeam
    {
        if (!$championship->isEnrollment()) {
            throw new \DomainException('Inscrições aceitas somente durante o período de enrollment.');
        }

        // Valida que a modalidade do time está nas modalidades do campeonato
        if (!$championship->sportModes()->where('sport_modes.id', $tsm->sport_mode_id)->exists()) {
            throw new \DomainException('A modalidade do time não é suportada por este campeonato.');
        }

        if ($championship->teams()->where('team_sport_mode_id', $tsm->id)->exists()) {
            throw new \DomainException('Este time já está inscrito no campeonato.');
        }

        return $championship->teams()->create(['team_sport_mode_id' => $tsm->id]);
    }

    public function removeTeam(Championship $championship, TeamSportMode $tsm): void
    {
        if (!$championship->isEnrollment()) {
            throw new \DomainException('Times só podem ser removidos durante o período de enrollment.');
        }

        $championship->teams()->where('team_sport_mode_id', $tsm->id)->delete();

        // Remove jogadores selecionados deste time
        ChampionshipTeamPlayer::where('championship_id', $championship->id)
            ->where('team_sport_mode_id', $tsm->id)
            ->delete();
    }

    public function selectPlayers(Championship $championship, TeamSportMode $tsm, array $membershipIds): void
    {
        if ($championship->status === ChampionshipStatus::Archived
            || $championship->status === ChampionshipStatus::Finished) {
            throw new \DomainException('Não é possível alterar jogadores em campeonatos encerrados.');
        }

        if (count($membershipIds) > $championship->max_players) {
            throw new \DomainException(
                "O campeonato permite no máximo {$championship->max_players} jogadores por time."
            );
        }

        // Remove seleção anterior e reinsere
        ChampionshipTeamPlayer::where('championship_id', $championship->id)
            ->where('team_sport_mode_id', $tsm->id)
            ->delete();

        foreach ($membershipIds as $mid) {
            ChampionshipTeamPlayer::create([
                'championship_id'      => $championship->id,
                'team_sport_mode_id'   => $tsm->id,
                'player_membership_id' => $mid,
            ]);
        }
    }
}
```

### 6.3 `ChampionshipMatchService`

```php
class ChampionshipMatchService
{
    public function __construct(private ChampionshipClosingService $closingService) {}

    public function registerResult(ChampionshipMatch $match, array $data): ChampionshipMatch
    {
        if ($match->isCompleted()) {
            throw new \DomainException('Resultado já registrado. Edição não permitida após completed.');
        }

        if ($match->match_status === MatchStatus::Cancelled) {
            throw new \DomainException('Não é possível registrar resultado em partida cancelada.');
        }

        $match->update(array_merge($data, ['match_status' => MatchStatus::Completed]));

        // Verifica auto-encerramento do campeonato
        $championship = $match->round->phase->championship;
        $this->maybeFinish($championship);

        return $match->fresh();
    }

    public function cancelMatch(ChampionshipMatch $match): ChampionshipMatch
    {
        if ($match->isCompleted()) {
            throw new \DomainException('Não é possível cancelar uma partida já encerrada.');
        }

        $match->update(['match_status' => MatchStatus::Cancelled]);

        $championship = $match->round->phase->championship;
        $this->maybeFinish($championship);

        return $match->fresh();
    }

    public function registerHighlights(ChampionshipMatch $match, array $items, int $ownerUserId): void
    {
        if (!$match->isCompleted()) {
            throw new \DomainException('Estatísticas só podem ser registradas em partidas encerradas.');
        }

        // Coleta IDs permitidos: jogadores do time do usuário nesta partida
        $allowedIds = ChampionshipTeamPlayer::where('championship_id', $match->round->phase->championship_id)
            ->whereIn('team_sport_mode_id', [$match->home_team_id, $match->away_team_id])
            ->whereHas('membership.teamSportMode.team', fn ($q) => $q->where('owner_id', $ownerUserId))
            ->pluck('player_membership_id')
            ->toArray();

        foreach ($items as $item) {
            if (!in_array($item['player_membership_id'], $allowedIds)) {
                throw new \DomainException(
                    "player_membership_id {$item['player_membership_id']} não pertence ao seu time neste campeonato."
                );
            }

            // Garante no máximo 1 MVP por partida
            if (!empty($item['is_mvp']) && $item['is_mvp']) {
                $match->highlights()->where('is_mvp', true)->update(['is_mvp' => false]);
            }

            ChampionshipMatchHighlight::updateOrCreate(
                [
                    'championship_match_id' => $match->id,
                    'player_membership_id'  => $item['player_membership_id'],
                ],
                $item,
            );
        }
    }

    private function maybeFinish(Championship $championship): void
    {
        if (!$championship->isActive()) {
            return;
        }

        $pendingMatches = ChampionshipMatch::whereHas(
            'round.phase', fn ($q) => $q->where('championship_id', $championship->id)
        )->whereNotIn('match_status', [MatchStatus::Completed->value, MatchStatus::Cancelled->value])
         ->exists();

        if (!$pendingMatches) {
            $this->closingService->finish($championship);
        }
    }
}
```

### 6.4 `ChampionshipClosingService`

```php
class ChampionshipClosingService
{
    public function finish(Championship $championship): void
    {
        DB::transaction(function () use ($championship) {
            $championship->update(['status' => ChampionshipStatus::Finished]);

            $this->calculateAwards($championship);
            $this->grantBadges($championship);
        });
    }

    private function calculateAwards(Championship $championship): void
    {
        $matchIds = ChampionshipMatch::whereHas(
            'round.phase', fn ($q) => $q->where('championship_id', $championship->id)
        )->pluck('id');

        // Artilheiro
        $topScorer = ChampionshipMatchHighlight::whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(goals) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->first();

        if ($topScorer?->total > 0) {
            $playerId = PlayerMembership::find($topScorer->player_membership_id)->player_id;
            ChampionshipAward::create([
                'championship_id' => $championship->id,
                'player_id'       => $playerId,
                'award_type'      => AwardType::TopScorer,
                'value'           => $topScorer->total,
            ]);
        }

        // Garçom
        $topAssist = ChampionshipMatchHighlight::whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(assists) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->first();

        if ($topAssist?->total > 0) {
            $playerId = PlayerMembership::find($topAssist->player_membership_id)->player_id;
            ChampionshipAward::create([
                'championship_id' => $championship->id,
                'player_id'       => $playerId,
                'award_type'      => AwardType::BestAssist,
                'value'           => $topAssist->total,
            ]);
        }

        // Bola de Ouro (mais MVPs)
        $goldenBall = ChampionshipMatchHighlight::whereIn('championship_match_id', $matchIds)
            ->where('is_mvp', true)
            ->selectRaw('player_membership_id, COUNT(*) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->first();

        if ($goldenBall) {
            $playerId = PlayerMembership::find($goldenBall->player_membership_id)->player_id;
            ChampionshipAward::create([
                'championship_id' => $championship->id,
                'player_id'       => $playerId,
                'award_type'      => AwardType::GoldenBall,
                'value'           => $goldenBall->total,
            ]);
        }

        // Fair Play (0 cartões em todo o campeonato)
        $fairPlay = ChampionshipMatchHighlight::whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(yellow_cards + red_cards) as cards')
            ->groupBy('player_membership_id')
            ->having('cards', 0)
            ->orderBy('player_membership_id')
            ->first();

        if ($fairPlay) {
            $playerId = PlayerMembership::find($fairPlay->player_membership_id)->player_id;
            ChampionshipAward::create([
                'championship_id' => $championship->id,
                'player_id'       => $playerId,
                'award_type'      => AwardType::FairPlay,
                'value'           => 0,
            ]);
        }
    }

    private function grantBadges(Championship $championship): void
    {
        $badgeMap = [
            AwardType::GoldenBall->value  => 'golden_ball',
            AwardType::TopScorer->value   => 'top_scorer',
            AwardType::BestAssist->value  => 'best_assist',
            AwardType::FairPlay->value    => 'fair_play',
        ];

        foreach ($championship->awards()->with('player')->get() as $award) {
            $badgeTypeName = $badgeMap[$award->award_type->value] ?? null;

            if (!$badgeTypeName) {
                continue;
            }

            $badgeType = BadgeType::where('name', $badgeTypeName)->first();

            if (!$badgeType) {
                continue;
            }

            PlayerBadge::create([
                'player_id'       => $award->player_id,
                'badge_type_id'   => $badgeType->id,
                'championship_id' => $championship->id,
                'awarded_at'      => now(),
                'notes'           => "Campeonato: {$championship->name}",
                'year'            => now()->year,
            ]);
        }
    }
}
```

---

## 7. Job — Arquivamento Automático

### `app/Jobs/ArchiveFinishedChampionships.php`

```php
class ArchiveFinishedChampionships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Championship::where('status', ChampionshipStatus::Finished)
            ->where('updated_at', '<=', now()->subDays(7))
            ->update(['status' => ChampionshipStatus::Archived]);
    }
}
```

Registrar no scheduler (`routes/console.php`):

```php
Schedule::job(new ArchiveFinishedChampionships)->daily();
```

---

## 8. Form Requests

Localização: `app/Http/Requests/Championship/`

### `StoreChampionshipRequest`

```php
public function rules(): array
{
    return [
        'name'            => ['required', 'string', 'max:45'],
        'description'     => ['nullable', 'string', 'max:255'],
        'location'        => ['nullable', 'string', 'max:150'],
        'starts_at'       => ['nullable', 'date'],
        'ends_at'         => ['nullable', 'date', 'after_or_equal:starts_at'],
        'format'          => ['required', Rule::in(['league'])],  // apenas league na Fase 3
        'max_players'     => ['integer', 'min:5', 'max:50'],
        'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
        'sport_mode_ids'  => ['required', 'array', 'min:1'],
        'sport_mode_ids.*' => ['integer', 'exists:sport_modes,id'],
    ];
}
```

### `UpdateChampionshipRequest`

```php
// Mesmas regras do Store; sem format (não pode alterar format após criação)
public function rules(): array
{
    return [
        'name'            => ['required', 'string', 'max:45'],
        'description'     => ['nullable', 'string', 'max:255'],
        'location'        => ['nullable', 'string', 'max:150'],
        'starts_at'       => ['nullable', 'date'],
        'ends_at'         => ['nullable', 'date', 'after_or_equal:starts_at'],
        'max_players'     => ['integer', 'min:5', 'max:50'],
        'category_id'     => ['nullable', 'integer', 'exists:categories,id'],
        'sport_mode_ids'  => ['array', 'min:1'],
        'sport_mode_ids.*' => ['integer', 'exists:sport_modes,id'],
    ];
}
```

### `EnrollTeamRequest`

```php
public function rules(): array
{
    return [
        'team_sport_mode_id' => ['required', 'integer', 'exists:team_sport_modes,id'],
    ];
}
```

### `SelectPlayersRequest`

```php
public function rules(): array
{
    return [
        'player_membership_ids'   => ['required', 'array', 'min:1'],
        'player_membership_ids.*' => ['integer', 'exists:player_memberships,id'],
    ];
}
```

### `RegisterChampionshipMatchResultRequest`

```php
public function rules(): array
{
    return [
        'home_goals'    => ['required', 'integer', 'min:0'],
        'away_goals'    => ['required', 'integer', 'min:0'],
        'scheduled_at'  => ['nullable', 'date'],
        'location'      => ['nullable', 'string', 'max:255'],
    ];
}
```

### `StoreChampionshipMatchHighlightsRequest`

```php
public function rules(): array
{
    return [
        'highlights'                        => ['required', 'array', 'min:1'],
        'highlights.*.player_membership_id' => ['required', 'integer', 'exists:player_memberships,id'],
        'highlights.*.goals'                => ['integer', 'min:0'],
        'highlights.*.assists'              => ['integer', 'min:0'],
        'highlights.*.yellow_cards'         => ['integer', 'min:0'],
        'highlights.*.red_cards'            => ['integer', 'min:0'],
        'highlights.*.is_mvp'               => ['boolean'],
    ];
}
```

---

## 9. API Resources

Localização: `app/Http/Resources/`

### `ChampionshipResource`

```php
class ChampionshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'location'    => $this->location,
            'starts_at'   => $this->starts_at?->toDateString(),
            'ends_at'     => $this->ends_at?->toDateString(),
            'format'      => $this->format,
            'status'      => $this->status,
            'max_players' => $this->max_players,
            'category'    => CategoryResource::make($this->whenLoaded('category')),
            'sport_modes' => SportModeResource::collection($this->whenLoaded('sportModes')),
            'teams'       => ChampionshipTeamResource::collection($this->whenLoaded('teams')),
            'creator'     => UserMinimalResource::make($this->whenLoaded('creator')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
```

### `ChampionshipMatchResource`

```php
class ChampionshipMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'round'        => [
                'id'           => $this->round->id,
                'name'         => $this->round->name,
                'round_number' => $this->round->round_number,
            ],
            'home_team'    => TeamSportModeResource::make($this->whenLoaded('homeTeam')),
            'away_team'    => TeamSportModeResource::make($this->whenLoaded('awayTeam')),
            'scheduled_at' => $this->scheduled_at,
            'location'     => $this->location,
            'match_status' => $this->match_status,
            'home_goals'   => $this->home_goals,
            'away_goals'   => $this->away_goals,
            'leg'          => $this->leg,
            'created_at'   => $this->created_at,
        ];
    }
}
```

### `ChampionshipMatchHighlightResource`

```php
class ChampionshipMatchHighlightResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'player_membership' => PlayerMembershipResource::make($this->whenLoaded('playerMembership')),
            'goals'             => $this->goals,
            'assists'           => $this->assists,
            'yellow_cards'      => $this->yellow_cards,
            'red_cards'         => $this->red_cards,
            'is_mvp'            => $this->is_mvp,
        ];
    }
}
```

### `ChampionshipStandingResource` (virtual — calculado ao vivo)

```php
// Não é um Model — é preenchido pelo ChampionshipService::standings()
class ChampionshipStandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'position'      => $this->resource['position'],
            'team'          => $this->resource['team'],     // TeamSportModeResource
            'played'        => $this->resource['played'],
            'wins'          => $this->resource['wins'],
            'draws'         => $this->resource['draws'],
            'losses'        => $this->resource['losses'],
            'goals_for'     => $this->resource['goals_for'],
            'goals_against' => $this->resource['goals_against'],
            'goal_diff'     => $this->resource['goal_diff'],
            'points'        => $this->resource['points'],
        ];
    }
}
```

### `ChampionshipAwardResource`

```php
class ChampionshipAwardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'award_type' => $this->award_type,
            'player'     => PlayerResource::make($this->whenLoaded('player')),
            'value'      => $this->value,
        ];
    }
}
```

---

## 10. Policy

Localização: `app/Policies/ChampionshipPolicy.php`

```php
class ChampionshipPolicy
{
    /** Editar configurações basicas (somente draft) */
    public function update(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by;
    }

    /** Excluir (somente draft) */
    public function delete(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by
            && $championship->isDraft();
    }

    /** Gerenciar lifecycle (abrir inscrições, ativar, cancelar, encerrar) */
    public function manageLifecycle(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by;
    }

    /** Gerenciar inscrições de times */
    public function manageEnrollment(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by;
    }

    /** Inscrever o próprio time (dono do time) */
    public function enroll(User $user, Championship $championship): bool
    {
        // Qualquer usuário dono de um time pode se inscrever
        return true;
    }

    /** Registrar resultado de partida */
    public function manageMatch(User $user, Championship $championship): bool
    {
        return $user->id === $championship->created_by;
    }
}
```

> Registrar em `AppServiceProvider` via `Gate::policy(Championship::class, ChampionshipPolicy::class)`.

---

## 11. API Controllers

Localização: `app/Http/Controllers/Api/`

Todos estendem `BaseController` e requerem `auth:sanctum`.

### 11.1 `ChampionshipController`

```
GET    /api/v1/championships                         → index
POST   /api/v1/championships                         → store
GET    /api/v1/championships/{championship}          → show
PUT    /api/v1/championships/{championship}          → update (somente draft, somente criador)
DELETE /api/v1/championships/{championship}          → destroy (somente draft, somente criador)
POST   /api/v1/championships/{championship}/open-enrollment → openEnrollment
POST   /api/v1/championships/{championship}/activate → activate
POST   /api/v1/championships/{championship}/cancel   → cancel
```

```php
class ChampionshipController extends BaseController
{
    public function __construct(private ChampionshipService $service) {}

    public function index(Request $request): JsonResponse
    {
        $championships = Championship::where('created_by', $request->user()->id)
            ->with('sportModes', 'category')
            ->latest()
            ->get();

        return $this->sendResponse(ChampionshipResource::collection($championships), 'Championships retrieved.');
    }

    public function store(StoreChampionshipRequest $request): JsonResponse
    {
        $championship = $this->service->create($request->validated(), $request->user());

        return $this->sendResponse(new ChampionshipResource($championship), 'Campeonato criado.', 201);
    }

    public function show(Championship $championship): JsonResponse
    {
        $championship->load(['sportModes', 'category', 'teams.teamSportMode.team', 'creator']);

        return $this->sendResponse(new ChampionshipResource($championship), 'Championship retrieved.');
    }

    public function update(UpdateChampionshipRequest $request, Championship $championship): JsonResponse
    {
        $this->authorize('update', $championship);
        $championship = $this->service->update($championship, $request->validated());

        return $this->sendResponse(new ChampionshipResource($championship), 'Campeonato atualizado.');
    }

    public function destroy(Request $request, Championship $championship): JsonResponse
    {
        $this->authorize('delete', $championship);
        $championship->delete();

        return $this->sendResponse([], 'Campeonato excluído.');
    }

    public function openEnrollment(Request $request, Championship $championship): JsonResponse
    {
        $this->authorize('manageLifecycle', $championship);
        $championship = $this->service->openEnrollment($championship);

        return $this->sendResponse(new ChampionshipResource($championship), 'Inscrições abertas.');
    }

    public function activate(Request $request, Championship $championship): JsonResponse
    {
        $this->authorize('manageLifecycle', $championship);
        $championship = $this->service->activate($championship);

        return $this->sendResponse(new ChampionshipResource($championship), 'Campeonato iniciado. Rodadas geradas automaticamente.');
    }

    public function cancel(Request $request, Championship $championship): JsonResponse
    {
        $this->authorize('manageLifecycle', $championship);
        $championship = $this->service->cancel($championship);

        return $this->sendResponse(new ChampionshipResource($championship), 'Campeonato cancelado.');
    }
}
```

### 11.2 `ChampionshipEnrollmentController`

```
GET    /api/v1/championships/{championship}/teams                                          → index
POST   /api/v1/championships/{championship}/teams                                          → enroll (dono de time inscreve seu time)
DELETE /api/v1/championships/{championship}/teams/{teamSportMode}                          → remove (organizador remove time)
GET    /api/v1/championships/{championship}/teams/{teamSportMode}/players                  → players index
POST   /api/v1/championships/{championship}/teams/{teamSportMode}/players                  → selectPlayers (dono do time)
```

```php
class ChampionshipEnrollmentController extends BaseController
{
    public function __construct(private ChampionshipEnrollmentService $enrollmentService) {}

    public function index(Championship $championship): JsonResponse
    {
        $championship->load('teams.teamSportMode.team');

        return $this->sendResponse(
            ChampionshipTeamResource::collection($championship->teams),
            'Teams retrieved.'
        );
    }

    public function enroll(EnrollTeamRequest $request, Championship $championship): JsonResponse
    {
        $tsm = TeamSportMode::where('id', $request->integer('team_sport_mode_id'))
            ->whereHas('team', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->firstOrFail();

        $enrolled = $this->enrollmentService->enroll($championship, $tsm);

        return $this->sendResponse($enrolled, 'Time inscrito.', 201);
    }

    public function removeTeam(Request $request, Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('manageEnrollment', $championship);
        $this->enrollmentService->removeTeam($championship, $teamSportMode);

        return $this->sendResponse([], 'Time removido do campeonato.');
    }

    public function players(Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        $players = ChampionshipTeamPlayer::where('championship_id', $championship->id)
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->with('membership.player.user', 'membership.position')
            ->get()
            ->pluck('membership');

        return $this->sendResponse(
            PlayerMembershipResource::collection($players),
            'Players retrieved.'
        );
    }

    public function selectPlayers(SelectPlayersRequest $request, Championship $championship, TeamSportMode $teamSportMode): JsonResponse
    {
        // Valida ownership do time
        if ($teamSportMode->team->owner_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }

        $this->enrollmentService->selectPlayers(
            $championship,
            $teamSportMode,
            $request->validated()['player_membership_ids'],
        );

        return $this->sendResponse([], 'Jogadores selecionados.');
    }
}
```

### 11.3 `ChampionshipMatchController`

```
GET    /api/v1/championships/{championship}/matches           → index
GET    /api/v1/championships/{championship}/matches/{match}   → show
PUT    /api/v1/championships/{championship}/matches/{match}   → update (result + schedule)
POST   /api/v1/championships/{championship}/matches/{match}/cancel → cancel
```

```php
class ChampionshipMatchController extends BaseController
{
    public function __construct(private ChampionshipMatchService $matchService) {}

    public function index(Championship $championship): JsonResponse
    {
        $matches = ChampionshipMatch::whereHas(
            'round.phase', fn ($q) => $q->where('championship_id', $championship->id)
        )
        ->with(['homeTeam.team', 'awayTeam.team', 'round'])
        ->orderBy('championship_round_id')
        ->get();

        return $this->sendResponse(ChampionshipMatchResource::collection($matches), 'Matches retrieved.');
    }

    public function show(Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $match->load(['homeTeam.team', 'awayTeam.team', 'round', 'highlights.playerMembership.player.user']);

        return $this->sendResponse(new ChampionshipMatchResource($match), 'Match retrieved.');
    }

    public function update(RegisterChampionshipMatchResultRequest $request, Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);
        $match = $this->matchService->registerResult($match, $request->validated());

        return $this->sendResponse(new ChampionshipMatchResource($match), 'Resultado registrado.');
    }

    public function cancel(Request $request, Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);
        $match = $this->matchService->cancelMatch($match);

        return $this->sendResponse(new ChampionshipMatchResource($match), 'Partida cancelada.');
    }
}
```

### 11.4 `ChampionshipMatchHighlightController`

```
GET    /api/v1/championships/{championship}/matches/{match}/highlights → index
POST   /api/v1/championships/{championship}/matches/{match}/highlights → store (bulk)
```

```php
class ChampionshipMatchHighlightController extends BaseController
{
    public function __construct(private ChampionshipMatchService $matchService) {}

    public function index(Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $highlights = $match->highlights()->with('playerMembership.player.user')->get();

        return $this->sendResponse(
            ChampionshipMatchHighlightResource::collection($highlights),
            'Highlights retrieved.'
        );
    }

    public function store(StoreChampionshipMatchHighlightsRequest $request, Championship $championship, ChampionshipMatch $match): JsonResponse
    {
        $this->authorize('manageMatch', $championship);

        $this->matchService->registerHighlights(
            $match,
            $request->validated()['highlights'],
            $request->user()->id,
        );

        return $this->sendResponse([], 'Estatísticas registradas.');
    }
}
```

### 11.5 `ChampionshipStandingsController`

```
GET    /api/v1/championships/{championship}/standings → index (classificação ao vivo)
GET    /api/v1/championships/{championship}/awards    → awards (prêmios após finished)
```

```php
class ChampionshipStandingsController extends BaseController
{
    public function standings(Championship $championship): JsonResponse
    {
        $teams = $championship->teams()->with('teamSportMode.team')->get();

        $matchIds = ChampionshipMatch::whereHas(
            'round.phase', fn ($q) => $q->where('championship_id', $championship->id)
        )->where('match_status', MatchStatus::Completed)->pluck('id');

        $standings = $teams->map(function ($ct) use ($matchIds) {
            $tsm = $ct->team_sport_mode_id;

            $homePlayed = ChampionshipMatch::whereIn('id', $matchIds)->where('home_team_id', $tsm)->get();
            $awayPlayed = ChampionshipMatch::whereIn('id', $matchIds)->where('away_team_id', $tsm)->get();

            $wins = $homePlayed->filter(fn ($m) => $m->home_goals > $m->away_goals)->count()
                  + $awayPlayed->filter(fn ($m) => $m->away_goals > $m->home_goals)->count();

            $draws = $homePlayed->filter(fn ($m) => $m->home_goals === $m->away_goals)->count()
                   + $awayPlayed->filter(fn ($m) => $m->home_goals === $m->away_goals)->count();

            $losses = $homePlayed->filter(fn ($m) => $m->home_goals < $m->away_goals)->count()
                    + $awayPlayed->filter(fn ($m) => $m->away_goals < $m->home_goals)->count();

            $gf = $homePlayed->sum('home_goals') + $awayPlayed->sum('away_goals');
            $gc = $homePlayed->sum('away_goals') + $awayPlayed->sum('home_goals');

            return [
                'team'          => $ct->teamSportMode,
                'played'        => $wins + $draws + $losses,
                'wins'          => $wins,
                'draws'         => $draws,
                'losses'        => $losses,
                'goals_for'     => $gf,
                'goals_against' => $gc,
                'goal_diff'     => $gf - $gc,
                'points'        => ($wins * 3) + $draws,
            ];
        })
        ->sortByDesc('points')
        ->sortByDesc('goal_diff')
        ->sortByDesc('goals_for')
        ->values()
        ->map(fn ($row, $idx) => array_merge($row, ['position' => $idx + 1]));

        return $this->sendResponse(
            ChampionshipStandingResource::collection($standings),
            'Standings retrieved.'
        );
    }

    public function awards(Championship $championship): JsonResponse
    {
        $awards = $championship->awards()->with('player.user')->get();

        return $this->sendResponse(
            ChampionshipAwardResource::collection($awards),
            'Awards retrieved.'
        );
    }
}
```

---

## 12. Rotas

`routes/api.php` — adicionado ao grupo `auth:sanctum` existente:

```php
// Campeonatos
Route::prefix('v1/championships')->name('api.championships.')->group(function () {
    Route::get('/',        [Api\ChampionshipController::class, 'index'])->name('index');
    Route::post('/',       [Api\ChampionshipController::class, 'store'])->name('store');
    Route::get('/{championship}',    [Api\ChampionshipController::class, 'show'])->name('show');
    Route::put('/{championship}',    [Api\ChampionshipController::class, 'update'])->name('update');
    Route::delete('/{championship}', [Api\ChampionshipController::class, 'destroy'])->name('destroy');

    // Lifecycle
    Route::post('/{championship}/open-enrollment', [Api\ChampionshipController::class, 'openEnrollment'])->name('open-enrollment');
    Route::post('/{championship}/activate',        [Api\ChampionshipController::class, 'activate'])->name('activate');
    Route::post('/{championship}/cancel',          [Api\ChampionshipController::class, 'cancel'])->name('cancel');

    // Inscrição de times
    Route::get('/{championship}/teams',    [Api\ChampionshipEnrollmentController::class, 'index'])->name('teams.index');
    Route::post('/{championship}/teams',   [Api\ChampionshipEnrollmentController::class, 'enroll'])->name('teams.enroll');
    Route::delete('/{championship}/teams/{teamSportMode}', [Api\ChampionshipEnrollmentController::class, 'removeTeam'])->name('teams.remove');

    // Seleção de jogadores
    Route::get('/{championship}/teams/{teamSportMode}/players',  [Api\ChampionshipEnrollmentController::class, 'players'])->name('teams.players.index');
    Route::post('/{championship}/teams/{teamSportMode}/players', [Api\ChampionshipEnrollmentController::class, 'selectPlayers'])->name('teams.players.select');

    // Partidas
    Route::get('/{championship}/matches',                         [Api\ChampionshipMatchController::class, 'index'])->name('matches.index');
    Route::get('/{championship}/matches/{match}',                 [Api\ChampionshipMatchController::class, 'show'])->name('matches.show');
    Route::put('/{championship}/matches/{match}',                 [Api\ChampionshipMatchController::class, 'update'])->name('matches.update');
    Route::post('/{championship}/matches/{match}/cancel',         [Api\ChampionshipMatchController::class, 'cancel'])->name('matches.cancel');

    // Highlights
    Route::get('/{championship}/matches/{match}/highlights',  [Api\ChampionshipMatchHighlightController::class, 'index'])->name('matches.highlights.index');
    Route::post('/{championship}/matches/{match}/highlights', [Api\ChampionshipMatchHighlightController::class, 'store'])->name('matches.highlights.store');

    // Classificação e prêmios
    Route::get('/{championship}/standings', [Api\ChampionshipStandingsController::class, 'standings'])->name('standings');
    Route::get('/{championship}/awards',    [Api\ChampionshipStandingsController::class, 'awards'])->name('awards');
});
```

---

## 13. Types TypeScript

Localização: `resources/js/types/`

### `types/championship.d.ts`

```ts
import type { SportMode } from './catalog/sport-mode';
import type { Category } from './catalog/category';
import type { TeamSportMode } from './team';
import type { PlayerMembership } from './player';
import type { UserMinimal } from './user';

export type ChampionshipStatus =
    | 'draft'
    | 'enrollment'
    | 'active'
    | 'finished'
    | 'archived'
    | 'cancelled';
export type ChampionshipFormat = 'league' | 'knockout' | 'cup';
export type MatchStatus = 'scheduled' | 'completed' | 'cancelled' | 'postponed';
export type AwardType =
    | 'golden_ball'
    | 'top_scorer'
    | 'best_assist'
    | 'best_goalkeeper'
    | 'fair_play';

export interface Championship {
    id: number;
    name: string;
    description: string | null;
    location: string | null;
    starts_at: string | null;
    ends_at: string | null;
    format: ChampionshipFormat;
    status: ChampionshipStatus;
    max_players: number;
    category: Category | null;
    sport_modes: SportMode[];
    creator: UserMinimal;
    created_at: string;
    updated_at: string;
}

export interface ChampionshipMatch {
    id: number;
    round: { id: number; name: string; round_number: number };
    home_team: TeamSportMode;
    away_team: TeamSportMode;
    scheduled_at: string | null;
    location: string | null;
    match_status: MatchStatus;
    home_goals: number | null;
    away_goals: number | null;
    leg: number;
    created_at: string;
}

export interface ChampionshipMatchHighlight {
    id: number;
    player_membership: PlayerMembership;
    goals: number;
    assists: number;
    yellow_cards: number;
    red_cards: number;
    is_mvp: boolean;
}

export interface ChampionshipStanding {
    position: number;
    team: TeamSportMode;
    played: number;
    wins: number;
    draws: number;
    losses: number;
    goals_for: number;
    goals_against: number;
    goal_diff: number;
    points: number;
}

export interface ChampionshipAward {
    id: number;
    award_type: AwardType;
    player: import('./player').Player;
    value: number | null;
}
```

---

## 14. Testes Feature

Localização: `tests/Feature/`

### 14.1 `ChampionshipTest`

```php
class ChampionshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_championship(): void
    {
        $user = User::factory()->create();
        $sm   = SportMode::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'           => 'Campeonato Verão',
            'format'         => 'league',
            'sport_mode_ids' => [$sm->id],
        ])->assertCreated()->assertJsonPath('data.status', 'draft');
    }

    public function test_only_league_format_allowed_in_phase_3(): void
    {
        $user = User::factory()->create();
        $sm   = SportMode::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'           => 'Copa',
            'format'         => 'knockout',
            'sport_mode_ids' => [$sm->id],
        ])->assertUnprocessable();
    }

    public function test_creator_can_open_enrollment(): void
    {
        $user = User::factory()->create();
        $c    = Championship::factory()->draft()->for($user, 'creator')->withSportMode()->create();

        $this->actingAs($user)
            ->postJson("/api/v1/championships/{$c->id}/open-enrollment")
            ->assertOk()
            ->assertJsonPath('data.status', 'enrollment');
    }

    public function test_cannot_activate_with_less_than_3_teams(): void
    {
        $user = User::factory()->create();
        $c    = Championship::factory()->enrollment()->for($user, 'creator')->create();
        ChampionshipTeam::factory()->count(2)->for($c)->create();

        $this->actingAs($user)
            ->postJson("/api/v1/championships/{$c->id}/activate")
            ->assertUnprocessable();
    }

    public function test_activate_generates_rounds_and_matches(): void
    {
        $user = User::factory()->create();
        $sm   = SportMode::factory()->create();
        $c    = Championship::factory()->enrollment()->for($user, 'creator')->withSportMode($sm)->create();

        $teams = TeamSportMode::factory()->count(4)->create(['sport_mode_id' => $sm->id]);
        foreach ($teams as $tsm) {
            ChampionshipTeam::factory()->create(['championship_id' => $c->id, 'team_sport_mode_id' => $tsm->id]);
        }

        $this->actingAs($user)
            ->postJson("/api/v1/championships/{$c->id}/activate")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        // 4 times → 3 rounds, 6 matches totais
        $this->assertDatabaseCount('championship_rounds', 3);
        $this->assertDatabaseCount('championship_matches', 6);
    }

    public function test_non_creator_cannot_manage_championship(): void
    {
        $other = User::factory()->create();
        $c     = Championship::factory()->draft()->create();

        $this->actingAs($other)
            ->postJson("/api/v1/championships/{$c->id}/open-enrollment")
            ->assertForbidden();
    }
}
```

### 14.2 `ChampionshipEnrollmentTest`

```php
class ChampionshipEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_owner_can_enroll_own_team(): void
    {
        $owner = User::factory()->create();
        $sm    = SportMode::factory()->create();
        $tsm   = TeamSportMode::factory()->create(['sport_mode_id' => $sm->id]);
        $tsm->team->update(['owner_id' => $owner->id]);
        $c = Championship::factory()->enrollment()->withSportMode($sm)->create();

        $this->actingAs($owner)->postJson("/api/v1/championships/{$c->id}/teams", [
            'team_sport_mode_id' => $tsm->id,
        ])->assertCreated();

        $this->assertDatabaseHas('championship_teams', [
            'championship_id'    => $c->id,
            'team_sport_mode_id' => $tsm->id,
        ]);
    }

    public function test_cannot_enroll_team_with_wrong_sport_mode(): void
    {
        $owner = User::factory()->create();
        $tsm   = TeamSportMode::factory()->create(); // modalidade diferente
        $tsm->team->update(['owner_id' => $owner->id]);
        $c = Championship::factory()->enrollment()->withSportMode()->create();

        $this->actingAs($owner)->postJson("/api/v1/championships/{$c->id}/teams", [
            'team_sport_mode_id' => $tsm->id,
        ])->assertUnprocessable();
    }

    public function test_owner_can_select_players_for_championship(): void
    {
        $owner  = User::factory()->create();
        $sm     = SportMode::factory()->create();
        $tsm    = TeamSportMode::factory()->create(['sport_mode_id' => $sm->id]);
        $tsm->team->update(['owner_id' => $owner->id]);
        $memberships = PlayerMembership::factory()->count(5)->create(['team_sport_mode_id' => $tsm->id]);
        $c = Championship::factory()->enrollment()->withSportMode($sm)->create();
        ChampionshipTeam::factory()->create(['championship_id' => $c->id, 'team_sport_mode_id' => $tsm->id]);

        $this->actingAs($owner)->postJson(
            "/api/v1/championships/{$c->id}/teams/{$tsm->id}/players",
            ['player_membership_ids' => $memberships->pluck('id')->toArray()]
        )->assertOk();

        $this->assertDatabaseCount('championship_team_players', 5);
    }
}
```

### 14.3 `ChampionshipMatchTest`

```php
class ChampionshipMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_register_match_result(): void
    {
        $user  = User::factory()->create();
        $c     = Championship::factory()->active()->for($user, 'creator')->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($c)->create();

        $this->actingAs($user)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 2, 'away_goals' => 1]
        )->assertOk()->assertJsonPath('data.match_status', 'completed');
    }

    public function test_cannot_edit_result_after_completed(): void
    {
        $user  = User::factory()->create();
        $c     = Championship::factory()->active()->for($user, 'creator')->create();
        $match = ChampionshipMatch::factory()->completed()->forChampionship($c)->create();

        $this->actingAs($user)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 3, 'away_goals' => 0]
        )->assertUnprocessable();
    }

    public function test_non_creator_cannot_register_result(): void
    {
        $other = User::factory()->create();
        $c     = Championship::factory()->active()->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($c)->create();

        $this->actingAs($other)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 1, 'away_goals' => 0]
        )->assertForbidden();
    }

    public function test_standings_reflect_registered_results(): void
    {
        $user  = User::factory()->create();
        $c     = Championship::factory()->active()->for($user, 'creator')->create();
        $home  = TeamSportMode::factory()->create();
        $away  = TeamSportMode::factory()->create();
        ChampionshipTeam::factory()->create(['championship_id' => $c->id, 'team_sport_mode_id' => $home->id]);
        ChampionshipTeam::factory()->create(['championship_id' => $c->id, 'team_sport_mode_id' => $away->id]);
        $match = ChampionshipMatch::factory()->completed()
                      ->forChampionship($c)
                      ->create(['home_team_id' => $home->id, 'away_team_id' => $away->id, 'home_goals' => 2, 'away_goals' => 0]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/championships/{$c->id}/standings")
            ->assertOk();

        $this->assertEquals(3, $response->json('data.0.points')); // vencedor com 3 pts
    }
}
```

### 14.4 `ChampionshipClosingTest`

```php
class ChampionshipClosingTest extends TestCase
{
    use RefreshDatabase;

    public function test_championship_auto_finishes_when_all_matches_completed(): void
    {
        $user  = User::factory()->create();
        $c     = Championship::factory()->active()->for($user, 'creator')->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($c)->create();

        // Registra o último (e único) resultado
        $this->actingAs($user)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 1, 'away_goals' => 0]
        )->assertOk();

        $this->assertEquals('finished', $c->fresh()->status->value);
    }

    public function test_awards_are_calculated_on_finish(): void
    {
        $user  = User::factory()->create();
        $c     = Championship::factory()->active()->for($user, 'creator')->create();
        $match = ChampionshipMatch::factory()->scheduled()->forChampionship($c)->create();
        $pm    = PlayerMembership::factory()->create(['team_sport_mode_id' => $match->home_team_id]);

        ChampionshipMatchHighlight::factory()->create([
            'championship_match_id' => $match->id,
            'player_membership_id'  => $pm->id,
            'goals'                 => 3,
        ]);

        $this->actingAs($user)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 3, 'away_goals' => 0]
        );

        $this->assertDatabaseHas('championship_awards', [
            'championship_id' => $c->id,
            'award_type'      => 'top_scorer',
        ]);
    }

    public function test_player_badges_are_granted_on_finish(): void
    {
        $user      = User::factory()->create();
        $c         = Championship::factory()->active()->for($user, 'creator')->create();
        $match     = ChampionshipMatch::factory()->scheduled()->forChampionship($c)->create();
        $player    = Player::factory()->create();
        $pm        = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $match->home_team_id,
            'player_id'          => $player->user_id,
        ]);
        BadgeType::factory()->create(['name' => 'top_scorer', 'scope' => 'championship']);

        ChampionshipMatchHighlight::factory()->create([
            'championship_match_id' => $match->id,
            'player_membership_id'  => $pm->id,
            'goals'                 => 2,
        ]);

        $this->actingAs($user)->putJson(
            "/api/v1/championships/{$c->id}/matches/{$match->id}",
            ['home_goals' => 2, 'away_goals' => 0]
        );

        $this->assertDatabaseHas('player_badges', [
            'player_id'       => $player->user_id,
            'championship_id' => $c->id,
        ]);
    }
}
```

### 14.5 `ArchiveFinishedChampionshipsTest`

```php
class ArchiveFinishedChampionshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_championships_older_than_7_days_are_archived(): void
    {
        $old   = Championship::factory()->finished()->create(['updated_at' => now()->subDays(8)]);
        $fresh = Championship::factory()->finished()->create(['updated_at' => now()->subDays(2)]);

        (new ArchiveFinishedChampionships)->handle();

        $this->assertEquals('archived', $old->fresh()->status->value);
        $this->assertEquals('finished', $fresh->fresh()->status->value);
    }
}
```

---

## 15. Factories

```
database/factories/
├── ChampionshipFactory.php
├── ChampionshipPhaseFactory.php
├── ChampionshipGroupFactory.php
├── ChampionshipRoundFactory.php
└── ChampionshipMatchFactory.php
```

### `ChampionshipFactory`

```php
public function definition(): array
{
    return [
        'created_by'  => User::factory(),
        'name'        => fake()->words(3, true) . ' Cup',
        'description' => fake()->sentence(),
        'format'      => ChampionshipFormat::League,
        'status'      => ChampionshipStatus::Draft,
        'max_players' => 20,
    ];
}

public function draft(): static
{
    return $this->state(['status' => ChampionshipStatus::Draft]);
}

public function enrollment(): static
{
    return $this->state(['status' => ChampionshipStatus::Enrollment]);
}

public function active(): static
{
    return $this->state(['status' => ChampionshipStatus::Active]);
}

public function finished(): static
{
    return $this->state(['status' => ChampionshipStatus::Finished]);
}

public function withSportMode(?SportMode $sm = null): static
{
    return $this->afterCreating(function (Championship $c) use ($sm) {
        $c->sportModes()->attach($sm ?? SportMode::factory()->create());
    });
}
```

### `ChampionshipMatchFactory`

```php
public function definition(): array
{
    return [
        'championship_round_id' => ChampionshipRound::factory(),
        'home_team_id'          => TeamSportMode::factory(),
        'away_team_id'          => TeamSportMode::factory(),
        'scheduled_at'          => now()->addDays(7),
        'match_status'          => MatchStatus::Scheduled,
        'leg'                   => 1,
    ];
}

public function scheduled(): static
{
    return $this->state(['match_status' => MatchStatus::Scheduled, 'home_goals' => null, 'away_goals' => null]);
}

public function completed(): static
{
    return $this->state([
        'match_status' => MatchStatus::Completed,
        'home_goals'   => fake()->numberBetween(0, 4),
        'away_goals'   => fake()->numberBetween(0, 4),
    ]);
}

public function forChampionship(Championship $championship): static
{
    return $this->afterMaking(function (ChampionshipMatch $match) use ($championship) {
        $phase = $championship->phases()->firstOrCreate([
            'name'        => 'Fase Principal',
            'type'        => PhaseType::GroupStage,
            'phase_order' => 1,
            'legs'        => 1,
        ]);

        $round = $phase->rounds()->firstOrCreate([
            'name'         => 'Rodada 1',
            'round_number' => 1,
        ]);

        $match->championship_round_id = $round->id;
    });
}
```

---

## 16. Checklist de Conclusão

### Banco

- [ ] Migration `championships`
- [ ] Migration `championship_sport_modes`
- [ ] Migration `championship_phases`
- [ ] Migration `championship_groups`
- [ ] Migration `championship_rounds`
- [ ] Migration `championship_teams`
- [ ] Migration `championship_group_entries`
- [ ] Migration `championship_team_players`
- [ ] Migration `championship_matches`
- [ ] Migration `championship_match_highlights`
- [ ] Migration `championship_awards`
- [ ] Migration `player_badges`

### Enums e Models

- [ ] Enum `ChampionshipStatus`
- [ ] Enum `ChampionshipFormat`
- [ ] Enum `PhaseType`
- [ ] Enum `AwardType`
- [ ] Model `Championship`
- [ ] Model `ChampionshipPhase`
- [ ] Model `ChampionshipGroup`
- [ ] Model `ChampionshipGroupEntry`
- [ ] Model `ChampionshipRound`
- [ ] Model `ChampionshipTeam`
- [ ] Model `ChampionshipTeamPlayer`
- [ ] Model `ChampionshipMatch`
- [ ] Model `ChampionshipMatchHighlight`
- [ ] Model `ChampionshipAward`
- [ ] Model `PlayerBadge`

### Backend

- [ ] `ChampionshipService` (incluindo `generateLeagueRounds`)
- [ ] `ChampionshipEnrollmentService`
- [ ] `ChampionshipMatchService` (incluindo `maybeFinish`)
- [ ] `ChampionshipClosingService` (awards + badges)
- [ ] Job `ArchiveFinishedChampionships` registrado no scheduler
- [ ] Form Requests: Store, Update, EnrollTeam, SelectPlayers, RegisterResult, StoreHighlights
- [ ] Resources: Championship, ChampionshipMatch, ChampionshipMatchHighlight, ChampionshipStanding, ChampionshipAward
- [ ] `ChampionshipPolicy` registrada em `AppServiceProvider`
- [ ] Controllers: Championship, Enrollment, Match, Highlight, Standings
- [ ] Rotas registradas em `routes/api.php`

### Frontend (Types)

- [ ] `types/championship.d.ts`

### Testes

- [ ] Factories: Championship, ChampionshipPhase, ChampionshipGroup, ChampionshipRound, ChampionshipMatch
- [ ] `ChampionshipTest`
- [ ] `ChampionshipEnrollmentTest`
- [ ] `ChampionshipMatchTest`
- [ ] `ChampionshipClosingTest`
- [ ] `ArchiveFinishedChampionshipsTest`
- [ ] Todos os testes passando (`php artisan test`)

---

## 17. Comandos de Referência

```bash
# Migrations
php artisan make:migration create_championships_table
php artisan make:migration create_championship_sport_modes_table
php artisan make:migration create_championship_phases_table
php artisan make:migration create_championship_groups_table
php artisan make:migration create_championship_rounds_table
php artisan make:migration create_championship_teams_table
php artisan make:migration create_championship_group_entries_table
php artisan make:migration create_championship_team_players_table
php artisan make:migration create_championship_matches_table
php artisan make:migration create_championship_match_highlights_table
php artisan make:migration create_championship_awards_table
php artisan make:migration create_player_badges_table

php artisan migrate

# Enums
php artisan make:enum Enums/ChampionshipStatus
php artisan make:enum Enums/ChampionshipFormat
php artisan make:enum Enums/PhaseType
php artisan make:enum Enums/AwardType

# Models
php artisan make:model Championship
php artisan make:model ChampionshipPhase
php artisan make:model ChampionshipGroup
php artisan make:model ChampionshipGroupEntry
php artisan make:model ChampionshipRound
php artisan make:model ChampionshipTeam
php artisan make:model ChampionshipTeamPlayer
php artisan make:model ChampionshipMatch
php artisan make:model ChampionshipMatchHighlight
php artisan make:model ChampionshipAward
php artisan make:model PlayerBadge

# Services
php artisan make:class Services/Championship/ChampionshipService
php artisan make:class Services/Championship/ChampionshipEnrollmentService
php artisan make:class Services/Championship/ChampionshipMatchService
php artisan make:class Services/Championship/ChampionshipClosingService

# Job
php artisan make:job ArchiveFinishedChampionships

# Form Requests
php artisan make:request Championship/StoreChampionshipRequest
php artisan make:request Championship/UpdateChampionshipRequest
php artisan make:request Championship/EnrollTeamRequest
php artisan make:request Championship/SelectPlayersRequest
php artisan make:request Championship/RegisterChampionshipMatchResultRequest
php artisan make:request Championship/StoreChampionshipMatchHighlightsRequest

# Resources
php artisan make:resource ChampionshipResource
php artisan make:resource ChampionshipMatchResource
php artisan make:resource ChampionshipMatchHighlightResource
php artisan make:resource ChampionshipStandingResource
php artisan make:resource ChampionshipAwardResource

# Policy
php artisan make:policy ChampionshipPolicy --model=Championship

# Controllers
php artisan make:controller Api/ChampionshipController
php artisan make:controller Api/ChampionshipEnrollmentController
php artisan make:controller Api/ChampionshipMatchController
php artisan make:controller Api/ChampionshipMatchHighlightController
php artisan make:controller Api/ChampionshipStandingsController

# Factories
php artisan make:factory ChampionshipFactory --model=Championship
php artisan make:factory ChampionshipPhaseFactory --model=ChampionshipPhase
php artisan make:factory ChampionshipGroupFactory --model=ChampionshipGroup
php artisan make:factory ChampionshipRoundFactory --model=ChampionshipRound
php artisan make:factory ChampionshipMatchFactory --model=ChampionshipMatch

# Testes
php artisan make:test Feature/ChampionshipTest
php artisan make:test Feature/ChampionshipEnrollmentTest
php artisan make:test Feature/ChampionshipMatchTest
php artisan make:test Feature/ChampionshipClosingTest
php artisan make:test Feature/ArchiveFinishedChampionshipsTest

php artisan test
```
