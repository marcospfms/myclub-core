# Roadmap Fase 2 — Amistosos

> Detalhamento completo de implementação da Fase 2. Cobertura: migrations, models, enums, services, jobs, notifications, form requests, resources, policy, controllers, rotas, types TypeScript e testes.
>
> **Pré‑requisito:** Fase 1 concluída (`team_sport_modes` e `player_memberships` disponíveis).
>
> Referências de schema: `docs/database/schema.md` §5.
> Referências de produto: `docs/product/friendly-match-flow.md`, `docs/product/authorization-rules.md`.
> Referências de padrões: `docs/patterns/`.

---

## 1. Escopo

| Item                                                       | Status      |
| ---------------------------------------------------------- | ----------- |
| Migration — `friendly_matches`                             | ✅ Concluído |
| Migration — `performance_highlights`                       | ✅ Concluído |
| Enums — `MatchConfirmation`, `MatchStatus`, `ResultStatus` | ✅ Concluído |
| Models — `FriendlyMatch`, `PerformanceHighlight`           | ✅ Concluído |
| Services — `FriendlyMatchService`, `MatchResultService`    | ✅ Concluído |
| Job — `ExpireFriendlyMatchInvitations`                     | ✅ Concluído |
| Notifications (5)                                          | ✅ Concluído |
| Form Requests (4)                                          | ✅ Concluído |
| API Resources (2)                                          | ✅ Concluído |
| Policy — `FriendlyMatchPolicy`                             | ✅ Concluído |
| Controllers (3)                                            | ✅ Concluído |
| Rotas API (`routes/api.php`)                               | ✅ Concluído |
| Types TypeScript                                           | ⬜ Pendente |
| Factories (6)                                              | ✅ Concluído |
| Testes Feature (4 classes)                                 | ✅ Concluído |

### Progresso atual

Blocos já implementados da Fase 2:

- migrations de amistosos
- migration de estatísticas individuais do amistoso
- enums de confirmação, estado da partida e confirmação de resultado
- models de amistosos e estatísticas individuais
- services de criação, confirmação e resultado de amistosos
- job de expiração de convites de amistoso
- notifications in-app via canal `database`
- form requests de criação, adiamento, resultado e highlights
- api resources de amistosos e destaques individuais
- policy de autorização para leitura, resposta, gestão e resultados
- controllers de amistosos, resultado e destaques individuais
- rotas públicas de leitura e rotas protegidas de gestão em `routes/api.php`
- factories de apoio para times, modalidades, jogadores, vínculos e amistosos
- testes feature da fase 2 validados com PHP 8.4 do WAMP (`17 passed`)

Próximo bloco recomendado:

- types TypeScript (quando a superfície consumidora precisar)

---

## 2. Contexto de Domínio

```
team_sport_modes (Fase 1)
 ├── friendly_matches (home_team_id / away_team_id)
 │    ├── confirmation   → ciclo de convite (pending → confirmed/rejected/expired)
 │    ├── match_status   → ciclo da partida (scheduled → completed/cancelled/postponed)
 │    ├── result_status  → confirmação bilateral do placar (none → pending → confirmed/disputed)
 │    └── performance_highlights → estatísticas individuais por jogador
 └── player_memberships (Fase 1) → referenciados em performance_highlights
```

Um amistoso é **sempre entre dois times diferentes** numa mesma modalidade (via `team_sport_modes`). Amistosos internos (mesmo time) não existem. O ciclo de vida envolve duas dimensões ortogonais: `confirmation` (logística de convite) e `match_status` (estado operacional da partida).

### Fluxo resumido

```
[Criação]
confirmation: pending / match_status: null

  ↓ time desafiado responde

confirmation: confirmed          confirmation: rejected / expired
match_status: scheduled          → encerrado sem partida

  ↓ partida acontece

match_status: completed          match_status: cancelled
(resultado confirmado bilateral) (cancelado após confirmação)

              ↑
      match_status: postponed
      (possível antes de completed)
```

---

## 3. Migrations

### 3.1 `create_friendly_matches_table`

```php
Schema::create('friendly_matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('home_team_id')->constrained('team_sport_modes')->restrictOnDelete();
    $table->foreignId('away_team_id')->constrained('team_sport_modes')->restrictOnDelete();
    $table->timestamp('scheduled_at')->nullable();
    $table->string('location', 255)->nullable();
    $table->enum('confirmation', ['pending', 'confirmed', 'rejected', 'expired'])->default('pending');
    $table->timestamp('invite_expires_at')->nullable();
    $table->enum('match_status', ['scheduled', 'completed', 'cancelled', 'postponed'])->nullable();
    $table->integer('home_goals')->nullable();
    $table->integer('away_goals')->nullable();
    $table->text('home_notes')->nullable();
    $table->text('away_notes')->nullable();
    $table->boolean('is_public')->default(false);
    $table->enum('result_status', ['none', 'pending', 'confirmed', 'disputed'])->default('none');
    $table->foreignId('result_registered_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

> `home_team_id` é o time desafiante; `away_team_id` é o time desafiado.
> `match_status` é `null` enquanto `confirmation ≠ confirmed`. Ao confirmar, passa automaticamente para `scheduled`.
> Ambos `home_team_id` e `away_team_id` devem pertencer à mesma `sport_mode_id` — validado na camada de service.

### 3.2 `create_performance_highlights_table`

```php
Schema::create('performance_highlights', function (Blueprint $table) {
    $table->id();
    $table->foreignId('friendly_match_id')->constrained()->cascadeOnDelete();
    $table->foreignId('player_membership_id')->constrained()->restrictOnDelete();
    $table->integer('goals')->default(0);
    $table->integer('assists')->default(0);
    $table->integer('yellow_cards')->default(0);
    $table->integer('red_cards')->default(0);
    $table->unique(['friendly_match_id', 'player_membership_id']);
    $table->timestamps();
});
```

> A unicidade composta `(friendly_match_id, player_membership_id)` garante um registro por jogador por amistoso.
> Inserções só são permitidas após `match_status = completed` — validado na camada de service.

---

## 4. Enums

### `app/Enums/MatchConfirmation.php`

```php
enum MatchConfirmation: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Rejected  = 'rejected';
    case Expired   = 'expired';
}
```

### `app/Enums/MatchStatus.php`

```php
enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Postponed = 'postponed';
}
```

### `app/Enums/ResultStatus.php`

```php
enum ResultStatus: string
{
    case None      = 'none';
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Disputed  = 'disputed';
}
```

---

## 5. Models

### 5.1 `FriendlyMatch`

```php
class FriendlyMatch extends Model
{
    protected $table = 'friendly_matches';

    protected $fillable = [
        'home_team_id', 'away_team_id', 'scheduled_at', 'location',
        'confirmation', 'invite_expires_at', 'match_status',
        'home_goals', 'away_goals', 'home_notes', 'away_notes',
        'is_public', 'result_status', 'result_registered_by',
    ];

    protected function casts(): array
    {
        return [
            'confirmation'      => MatchConfirmation::class,
            'match_status'      => MatchStatus::class,
            'result_status'     => ResultStatus::class,
            'scheduled_at'      => 'datetime',
            'invite_expires_at' => 'datetime',
            'is_public'         => 'boolean',
        ];
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class, 'away_team_id');
    }

    public function resultRegisteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'result_registered_by');
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PerformanceHighlight::class);
    }

    public function isPending(): bool
    {
        return $this->confirmation === MatchConfirmation::Pending;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmation === MatchConfirmation::Confirmed;
    }

    public function isCompleted(): bool
    {
        return $this->match_status === MatchStatus::Completed;
    }
}
```

### 5.2 `PerformanceHighlight`

```php
class PerformanceHighlight extends Model
{
    protected $table = 'performance_highlights';

    protected $fillable = [
        'friendly_match_id', 'player_membership_id',
        'goals', 'assists', 'yellow_cards', 'red_cards',
    ];

    protected function casts(): array
    {
        return [
            'goals'        => 'integer',
            'assists'      => 'integer',
            'yellow_cards' => 'integer',
            'red_cards'    => 'integer',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FriendlyMatch::class, 'friendly_match_id');
    }

    public function playerMembership(): BelongsTo
    {
        return $this->belongsTo(PlayerMembership::class);
    }
}
```

---

## 6. Services

Localização: `app/Services/`

| Service                | Arquivo                                               |
| ---------------------- | ----------------------------------------------------- |
| `FriendlyMatchService` | `app/Services/FriendlyMatch/FriendlyMatchService.php` |
| `MatchResultService`   | `app/Services/FriendlyMatch/MatchResultService.php`   |

### 6.1 `FriendlyMatchService`

```php
class FriendlyMatchService
{
    public function create(array $data, User $challenger): FriendlyMatch
    {
        $homeTeam = TeamSportMode::findOrFail($data['home_team_id']);
        $awayTeam = TeamSportMode::findOrFail($data['away_team_id']);

        if ($homeTeam->sport_mode_id !== $awayTeam->sport_mode_id) {
            throw new \DomainException('Os times devem competir na mesma modalidade esportiva.');
        }

        if ($homeTeam->id === $awayTeam->id) {
            throw new \DomainException('Um time não pode desafiar a si mesmo.');
        }

        return FriendlyMatch::create(array_merge($data, [
            'confirmation'      => MatchConfirmation::Pending,
            'match_status'      => null,
            'result_status'     => ResultStatus::None,
            'invite_expires_at' => now()->addDays(2),
        ]));
    }

    public function confirm(FriendlyMatch $match): FriendlyMatch
    {
        if (!$match->isPending()) {
            throw new \DomainException('Amistoso não está pendente de confirmação.');
        }

        $match->update([
            'confirmation' => MatchConfirmation::Confirmed,
            'match_status' => MatchStatus::Scheduled,
        ]);

        return $match->fresh();
    }

    public function reject(FriendlyMatch $match): FriendlyMatch
    {
        if (!$match->isPending()) {
            throw new \DomainException('Amistoso não está pendente de confirmação.');
        }

        $match->update(['confirmation' => MatchConfirmation::Rejected]);

        return $match->fresh();
    }

    public function cancel(FriendlyMatch $match): FriendlyMatch
    {
        if (!$match->isConfirmed()) {
            throw new \DomainException('Apenas amistosos confirmados podem ser cancelados. Para remover um convite pendente, use DELETE /friendly-matches/{id}.');
        }

        if ($match->isCompleted()) {
            throw new \DomainException('Não é possível cancelar um amistoso já encerrado.');
        }

        $match->update(['match_status' => MatchStatus::Cancelled]);

        return $match->fresh();
    }

    public function postpone(FriendlyMatch $match, array $data): FriendlyMatch
    {
        if (!$match->isConfirmed()) {
            throw new \DomainException('Apenas amistosos confirmados podem ser adiados.');
        }

        if ($match->isCompleted()) {
            throw new \DomainException('Não é possível adiar um amistoso já encerrado.');
        }

        $match->update([
            'match_status' => MatchStatus::Postponed,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        return $match->fresh();
    }

    public function removePendingInvite(FriendlyMatch $match): void
    {
        if (!$match->isPending()) {
            throw new \DomainException('Apenas convites pendentes podem ser removidos.');
        }

        $match->delete();
    }
}
```

### 6.2 `MatchResultService`

```php
class MatchResultService
{
    public function register(FriendlyMatch $match, array $data, User $registeredBy): FriendlyMatch
    {
        if ($match->match_status !== MatchStatus::Scheduled
            && $match->match_status !== MatchStatus::Postponed) {
            throw new \DomainException('Resultado só pode ser registrado em amistosos confirmados.');
        }

        if ($match->isCompleted()) {
            throw new \DomainException('Resultado já confirmado. Edição não permitida.');
        }

        $match->update([
            'home_goals'           => $data['home_goals'],
            'away_goals'           => $data['away_goals'],
            'result_status'        => ResultStatus::Pending,
            'result_registered_by' => $registeredBy->id,
        ]);

        return $match->fresh();
    }

    public function confirmResult(FriendlyMatch $match): FriendlyMatch
    {
        if ($match->result_status !== ResultStatus::Pending) {
            throw new \DomainException('Resultado não está aguardando confirmação.');
        }

        $match->update([
            'result_status' => ResultStatus::Confirmed,
            'match_status'  => MatchStatus::Completed,
        ]);

        return $match->fresh();
    }

    public function disputeResult(FriendlyMatch $match): FriendlyMatch
    {
        if ($match->result_status !== ResultStatus::Pending) {
            throw new \DomainException('Resultado não está aguardando confirmação.');
        }

        $match->update([
            'result_status'        => ResultStatus::Disputed,
            'home_goals'           => null,
            'away_goals'           => null,
            'result_registered_by' => null,
        ]);

        return $match->fresh();
    }

    public function registerHighlight(FriendlyMatch $match, array $item): PerformanceHighlight
    {
        if (!$match->isCompleted()) {
            throw new \DomainException('Estatísticas só podem ser registradas após o encerramento do amistoso.');
        }

        return PerformanceHighlight::updateOrCreate(
            [
                'friendly_match_id'    => $match->id,
                'player_membership_id' => $item['player_membership_id'],
            ],
            $item,
        );
    }
}
```

---

## 7. Job — Expiração de Convites

### `app/Jobs/ExpireFriendlyMatchInvitations.php`

```php
class ExpireFriendlyMatchInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        FriendlyMatch::where('confirmation', MatchConfirmation::Pending)
            ->where('invite_expires_at', '<=', now())
            ->update(['confirmation' => MatchConfirmation::Expired]);
    }
}
```

Registrar no scheduler (`routes/console.php`):

```php
Schedule::job(new ExpireFriendlyMatchInvitations)->hourly();
```

---

## 8. Notifications

Localização: `app/Notifications/FriendlyMatch/`

| Notification                         | Destinatário                 | Gatilho                         |
| ------------------------------------ | ---------------------------- | ------------------------------- |
| `FriendlyMatchInvitedNotification`   | Dono do time desafiado       | Criação do amistoso             |
| `FriendlyMatchConfirmedNotification` | Dono do time desafiante      | Convite confirmado              |
| `FriendlyMatchRejectedNotification`  | Dono do time desafiante      | Convite recusado                |
| `MatchResultRegisteredNotification`  | O outro time (não registrou) | Resultado registrado            |
| `MatchResultConfirmedNotification`   | Dono que registrou o placar  | Resultado confirmado pelo outro |

> Todas as notificações usam o canal `database` (in-app web). Canal `broadcast` para push mobile é previsto na **Fase 8**.

```php
// Exemplo: FriendlyMatchInvitedNotification
class FriendlyMatchInvitedNotification extends Notification
{
    public function __construct(public readonly FriendlyMatch $match) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'match_id'       => $this->match->id,
            'home_team_name' => $this->match->homeTeam->team->name,
            'scheduled_at'   => $this->match->scheduled_at,
        ];
    }
}
```

---

## 9. Form Requests

Localização: `app/Http/Requests/FriendlyMatch/`

### `StoreFriendlyMatchRequest`

```php
public function rules(): array
{
    return [
        'home_team_id' => ['required', 'integer', 'exists:team_sport_modes,id'],
        'away_team_id' => ['required', 'integer', 'exists:team_sport_modes,id', 'different:home_team_id'],
        'scheduled_at' => ['required', 'date', 'after:now'],
        'location'     => ['nullable', 'string', 'max:255'],
        'is_public'    => ['boolean'],
    ];
}
```

> O controller valida que `home_team_id` pertence ao usuário autenticado antes de repassar ao service.

### `PostponeFriendlyMatchRequest`

```php
public function rules(): array
{
    return [
        'scheduled_at' => ['nullable', 'date', 'after:now'],
    ];
}
```

### `RegisterMatchResultRequest`

```php
public function rules(): array
{
    return [
        'home_goals' => ['required', 'integer', 'min:0'],
        'away_goals' => ['required', 'integer', 'min:0'],
    ];
}
```

### `StorePerformanceHighlightRequest`

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
    ];
}
```

> A request aceita um array de highlights (envio em lote). O service valida que cada `player_membership_id` pertence ao time do usuário autenticado.

---

## 10. API Resources

Localização:

- `app/Http/Resources/FriendlyMatch/FriendlyMatchResource.php`
- `app/Http/Resources/FriendlyMatch/PerformanceHighlightResource.php`

Regras aplicadas neste bloco:

- chaves em `snake_case`
- relacionamentos expostos apenas via `whenLoaded()`
- `home_notes` e `away_notes` visíveis somente para o dono do respectivo time ou `admin`
- `confirmation`, `match_status` e `result_status` serializados via `enum->value`

---

## 11. Policy

Localização: `app/Policies/FriendlyMatchPolicy.php`

Arquivos implementados:

- `app/Policies/FriendlyMatchPolicy.php`
- `app/Providers/AppServiceProvider.php`

Regras aplicadas neste bloco:

- `admin` pode visualizar e gerenciar qualquer amistoso
- amistoso privado só pode ser visualizado pelos donos dos times envolvidos ou `admin`
- somente o dono do time desafiante remove convite pendente
- somente o dono do time desafiado responde convite
- gestão de cancelamento, adiamento, resultado e highlights fica com os donos dos times participantes

---

## 12. API Controllers

Localização:

- `app/Http/Controllers/Api/V1/FriendlyMatch/FriendlyMatchController.php`
- `app/Http/Controllers/Api/V1/FriendlyMatch/MatchResultController.php`
- `app/Http/Controllers/Api/V1/FriendlyMatch/PerformanceHighlightController.php`

Todos estendem `BaseController`, usam `Resource` e assumem `auth:sanctum`.

Endpoints cobertos neste bloco:

- `GET /api/v1/friendly-matches`
- `POST /api/v1/friendly-matches`
- `GET /api/v1/friendly-matches/{match}`
- `DELETE /api/v1/friendly-matches/{match}`
- `POST /api/v1/friendly-matches/{match}/confirm`
- `POST /api/v1/friendly-matches/{match}/reject`
- `POST /api/v1/friendly-matches/{match}/cancel`
- `POST /api/v1/friendly-matches/{match}/postpone`
- `POST /api/v1/friendly-matches/{match}/result`
- `POST /api/v1/friendly-matches/{match}/result/confirm`
- `POST /api/v1/friendly-matches/{match}/result/dispute`
- `GET /api/v1/friendly-matches/{match}/highlights`
- `POST /api/v1/friendly-matches/{match}/highlights`

Regras aplicadas neste bloco:

- conflitos de domínio retornam `409` via `sendError()`
- autorização usa `FriendlyMatchPolicy`
- serialização usa `FriendlyMatchResource` e `PerformanceHighlightResource`
- listagem e carregamento principal do amistoso foram empurrados para `FriendlyMatchService`
- registro de resultado agora respeita `home_notes` e `away_notes` conforme o lado do usuário que registrou

---

## 13. Rotas

Arquivo implementado:

- `routes/api.php`

Estratégia aplicada:

- `GET /api/v1/friendly-matches/{match}` é público, mas depende de `FriendlyMatchPolicy::view`
- `GET /api/v1/friendly-matches/{match}/highlights` é público, mas depende de `FriendlyMatchPolicy::view`
- listagem, criação, resposta ao convite, gestão do amistoso, resultado e gravação de highlights exigem `auth:sanctum`
- as rotas foram registradas dentro do prefixo já existente `api.v1`

---

## 14. Types TypeScript

Localização: `resources/js/types/`

### `types/friendly-match.d.ts`

```ts
import type { TeamSportMode } from './team';
import type { PlayerMembership } from './player';

export type MatchConfirmation =
    | 'pending'
    | 'confirmed'
    | 'rejected'
    | 'expired';
export type MatchStatus = 'scheduled' | 'completed' | 'cancelled' | 'postponed';
export type ResultStatus = 'none' | 'pending' | 'confirmed' | 'disputed';

export interface FriendlyMatch {
    id: number;
    home_team: TeamSportMode;
    away_team: TeamSportMode;
    scheduled_at: string | null;
    location: string | null;
    confirmation: MatchConfirmation;
    invite_expires_at: string | null;
    match_status: MatchStatus | null;
    home_goals: number | null;
    away_goals: number | null;
    home_notes: string | null; // visível apenas para os donos dos dois times
    away_notes: string | null; // visível apenas para os donos dos dois times
    is_public: boolean;
    result_status: ResultStatus;
    created_at: string;
    updated_at: string;
}

export interface PerformanceHighlight {
    id: number;
    player_membership: PlayerMembership;
    goals: number;
    assists: number;
    yellow_cards: number;
    red_cards: number;
}
```

---

## 15. Testes Feature

Localização: `tests/Feature/`

### 15.1 `FriendlyMatchTest`

```php
class FriendlyMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_friendly_match(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $awayTsm   = TeamSportMode::factory()->create(['sport_mode_id' => $homeTsm->sport_mode_id]);

        $this->actingAs($homeOwner)->postJson('/api/v1/friendly-matches', [
            'home_team_id' => $homeTsm->id,
            'away_team_id' => $awayTsm->id,
            'scheduled_at' => now()->addDays(7)->toDateTimeString(),
            'location'     => 'Estádio Central',
        ])->assertCreated()->assertJsonPath('data.confirmation', 'pending');
    }

    public function test_cannot_challenge_with_different_sport_modes(): void
    {
        $owner   = User::factory()->create();
        $team    = Team::factory()->create(['owner_id' => $owner->id]);
        $homeTsm = TeamSportMode::factory()->create(['team_id' => $team->id]);
        $awayTsm = TeamSportMode::factory()->create(); // modalidade diferente

        $this->actingAs($owner)->postJson('/api/v1/friendly-matches', [
            'home_team_id' => $homeTsm->id,
            'away_team_id' => $awayTsm->id,
            'scheduled_at' => now()->addDays(7)->toDateTimeString(),
        ])->assertUnprocessable();
    }

    public function test_away_owner_can_confirm_match(): void
    {
        $awayOwner = User::factory()->create();
        $awayTeam  = Team::factory()->create(['owner_id' => $awayOwner->id]);
        $awayTsm   = TeamSportMode::factory()->create(['team_id' => $awayTeam->id]);
        $match     = FriendlyMatch::factory()->pending()->create(['away_team_id' => $awayTsm->id]);

        $this->actingAs($awayOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/confirm")
            ->assertOk()
            ->assertJsonPath('data.confirmation', 'confirmed')
            ->assertJsonPath('data.match_status', 'scheduled');
    }

    public function test_home_owner_cannot_confirm_own_match(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $match     = FriendlyMatch::factory()->pending()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/confirm")
            ->assertForbidden();
    }

    public function test_home_owner_can_remove_pending_invite(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $match     = FriendlyMatch::factory()->pending()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->deleteJson("/api/v1/friendly-matches/{$match->id}")
            ->assertOk();

        $this->assertDatabaseMissing('friendly_matches', ['id' => $match->id]);
    }

    public function test_either_owner_can_cancel_confirmed_match(): void
    {
        $awayOwner = User::factory()->create();
        $awayTeam  = Team::factory()->create(['owner_id' => $awayOwner->id]);
        $awayTsm   = TeamSportMode::factory()->create(['team_id' => $awayTeam->id]);
        $match     = FriendlyMatch::factory()->scheduled()->create(['away_team_id' => $awayTsm->id]);

        $this->actingAs($awayOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.match_status', 'cancelled');
    }

    public function test_friendly_match_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/friendly-matches')->assertUnauthorized();
    }
}
```

### 15.2 `MatchResultTest`

```php
class MatchResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_register_result(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $match     = FriendlyMatch::factory()->scheduled()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/result", [
                'home_goals' => 2,
                'away_goals' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.result_status', 'pending');
    }

    public function test_other_owner_can_confirm_result(): void
    {
        $homeOwner = User::factory()->create();
        $awayOwner = User::factory()->create();
        $awayTeam  = Team::factory()->create(['owner_id' => $awayOwner->id]);
        $awayTsm   = TeamSportMode::factory()->create(['team_id' => $awayTeam->id]);
        $match     = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'away_team_id' => $awayTsm->id,
        ]);

        $this->actingAs($awayOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/result/confirm")
            ->assertOk()
            ->assertJsonPath('data.result_status', 'confirmed')
            ->assertJsonPath('data.match_status', 'completed');
    }

    public function test_registrar_cannot_confirm_own_result(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $match     = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'home_team_id' => $homeTsm->id,
        ]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/result/confirm")
            ->assertForbidden();
    }

    public function test_other_owner_can_dispute_result(): void
    {
        $homeOwner = User::factory()->create();
        $awayOwner = User::factory()->create();
        $awayTeam  = Team::factory()->create(['owner_id' => $awayOwner->id]);
        $awayTsm   = TeamSportMode::factory()->create(['team_id' => $awayTeam->id]);
        $match     = FriendlyMatch::factory()->withPendingResult($homeOwner)->create([
            'away_team_id' => $awayTsm->id,
        ]);

        $this->actingAs($awayOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/result/dispute")
            ->assertOk()
            ->assertJsonPath('data.result_status', 'disputed');
    }

    public function test_cannot_edit_result_after_completed(): void
    {
        $homeOwner = User::factory()->create();
        $homeTeam  = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm   = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $match     = FriendlyMatch::factory()->completed()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/result", [
                'home_goals' => 3,
                'away_goals' => 0,
            ])
            ->assertUnprocessable();
    }
}
```

### 15.3 `PerformanceHighlightTest`

```php
class PerformanceHighlightTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_register_highlights_for_own_players(): void
    {
        $homeOwner  = User::factory()->create();
        $homeTeam   = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm    = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $membership = PlayerMembership::factory()->create(['team_sport_mode_id' => $homeTsm->id]);
        $match      = FriendlyMatch::factory()->completed()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/highlights", [
                'highlights' => [[
                    'player_membership_id' => $membership->id,
                    'goals'                => 2,
                    'assists'              => 1,
                    'yellow_cards'         => 0,
                    'red_cards'            => 0,
                ]],
            ])
            ->assertOk();

        $this->assertDatabaseHas('performance_highlights', [
            'friendly_match_id'    => $match->id,
            'player_membership_id' => $membership->id,
            'goals'                => 2,
        ]);
    }

    public function test_owner_cannot_register_highlights_for_opponent_players(): void
    {
        $homeOwner      = User::factory()->create();
        $homeTeam       = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm        = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $awayTsm        = TeamSportMode::factory()->create();
        $awayMembership = PlayerMembership::factory()->create(['team_sport_mode_id' => $awayTsm->id]);
        $match          = FriendlyMatch::factory()->completed()->create([
            'home_team_id' => $homeTsm->id,
            'away_team_id' => $awayTsm->id,
        ]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/highlights", [
                'highlights' => [['player_membership_id' => $awayMembership->id, 'goals' => 1]],
            ])
            ->assertForbidden();
    }

    public function test_cannot_register_highlights_before_match_completed(): void
    {
        $homeOwner  = User::factory()->create();
        $homeTeam   = Team::factory()->create(['owner_id' => $homeOwner->id]);
        $homeTsm    = TeamSportMode::factory()->create(['team_id' => $homeTeam->id]);
        $membership = PlayerMembership::factory()->create(['team_sport_mode_id' => $homeTsm->id]);
        $match      = FriendlyMatch::factory()->scheduled()->create(['home_team_id' => $homeTsm->id]);

        $this->actingAs($homeOwner)
            ->postJson("/api/v1/friendly-matches/{$match->id}/highlights", [
                'highlights' => [['player_membership_id' => $membership->id, 'goals' => 1]],
            ])
            ->assertUnprocessable();
    }
}
```

### 15.4 `ExpireFriendlyMatchInvitationsTest`

```php
class ExpireFriendlyMatchInvitationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_invites_are_marked_as_expired(): void
    {
        $expired = FriendlyMatch::factory()->create([
            'confirmation'      => 'pending',
            'invite_expires_at' => now()->subDay(),
        ]);

        $active = FriendlyMatch::factory()->create([
            'confirmation'      => 'pending',
            'invite_expires_at' => now()->addDay(),
        ]);

        (new ExpireFriendlyMatchInvitations)->handle();

        $this->assertEquals('expired', $expired->fresh()->confirmation->value);
        $this->assertEquals('pending', $active->fresh()->confirmation->value);
    }
}
```

---

## 16. Factories

Arquivos implementados:

- `database/factories/TeamFactory.php`
- `database/factories/TeamSportModeFactory.php`
- `database/factories/PlayerFactory.php`
- `database/factories/PlayerMembershipFactory.php`
- `database/factories/FriendlyMatchFactory.php`
- `database/factories/PerformanceHighlightFactory.php`

Cobertura entregue neste bloco:

- base reutilizável para os testes de times e amistosos
- `FriendlyMatchFactory` com estados `pending()`, `scheduled()`, `completed()` e `withPendingResult()`
- garantia de que o `away_team_id` do amistoso nasce em modalidade compatível com o `home_team_id`
- `PlayerMembershipFactory` com estados `starter()` e `inactive()`

---

## 17. Checklist de Conclusão

### Banco

- [ ] Migration `friendly_matches`
- [ ] Migration `performance_highlights`

### Enums e Models

- [ ] Enum `MatchConfirmation`
- [ ] Enum `MatchStatus`
- [ ] Enum `ResultStatus`
- [ ] Model `FriendlyMatch`
- [ ] Model `PerformanceHighlight`

### Backend

- [ ] `FriendlyMatchService`
- [ ] `MatchResultService`
- [ ] Job `ExpireFriendlyMatchInvitations` registrado no scheduler (`routes/console.php`)
- [ ] Notificações: `FriendlyMatchInvitedNotification`, `FriendlyMatchConfirmedNotification`, `FriendlyMatchRejectedNotification`, `MatchResultRegisteredNotification`, `MatchResultConfirmedNotification`
- [ ] Form Requests: `StoreFriendlyMatchRequest`, `PostponeFriendlyMatchRequest`, `RegisterMatchResultRequest`, `StorePerformanceHighlightRequest`
- [ ] Resources: `FriendlyMatchResource`, `PerformanceHighlightResource`
- [ ] `FriendlyMatchPolicy` registrada em `AppServiceProvider`
- [ ] Controllers: `FriendlyMatchController`, `MatchResultController`, `PerformanceHighlightController`
- [ ] Rotas registradas em `routes/api.php`

### Frontend (Types)

- [ ] `types/friendly-match.d.ts`

### Testes

- [x] Factories: `TeamFactory`, `TeamSportModeFactory`, `PlayerFactory`, `PlayerMembershipFactory`, `FriendlyMatchFactory`, `PerformanceHighlightFactory`
- [x] `FriendlyMatchApiTest`
- [x] `MatchResultApiTest`
- [x] `PerformanceHighlightApiTest`
- [x] `ExpireFriendlyMatchInvitationsTest`
- [x] Testes da fase 2 passando (`17 passed`, `51 assertions`)

---

## 18. Comandos de Referência

```bash
# Migrations
php artisan make:migration create_friendly_matches_table
php artisan make:migration create_performance_highlights_table
php artisan migrate

# Enums
php artisan make:enum Enums/MatchConfirmation
php artisan make:enum Enums/MatchStatus
php artisan make:enum Enums/ResultStatus

# Models
php artisan make:model FriendlyMatch
php artisan make:model PerformanceHighlight

# Services
php artisan make:class Services/FriendlyMatch/FriendlyMatchService
php artisan make:class Services/FriendlyMatch/MatchResultService

# Job
php artisan make:job ExpireFriendlyMatchInvitations

# Notifications
php artisan make:notification FriendlyMatch/FriendlyMatchInvitedNotification
php artisan make:notification FriendlyMatch/FriendlyMatchConfirmedNotification
php artisan make:notification FriendlyMatch/FriendlyMatchRejectedNotification
php artisan make:notification FriendlyMatch/MatchResultRegisteredNotification
php artisan make:notification FriendlyMatch/MatchResultConfirmedNotification

# Form Requests
php artisan make:request FriendlyMatch/StoreFriendlyMatchRequest
php artisan make:request FriendlyMatch/PostponeFriendlyMatchRequest
php artisan make:request FriendlyMatch/RegisterMatchResultRequest
php artisan make:request FriendlyMatch/StorePerformanceHighlightRequest

# Resources
php artisan make:resource FriendlyMatchResource
php artisan make:resource PerformanceHighlightResource

# Policy
php artisan make:policy FriendlyMatchPolicy --model=FriendlyMatch

# Controllers
php artisan make:controller Api/FriendlyMatchController
php artisan make:controller Api/MatchResultController
php artisan make:controller Api/PerformanceHighlightController

# Factories
php artisan make:factory FriendlyMatchFactory --model=FriendlyMatch
php artisan make:factory PerformanceHighlightFactory --model=PerformanceHighlight

# Testes
php artisan make:test Feature/FriendlyMatchTest
php artisan make:test Feature/MatchResultTest
php artisan make:test Feature/PerformanceHighlightTest
php artisan make:test Feature/ExpireFriendlyMatchInvitationsTest

php artisan test
```
