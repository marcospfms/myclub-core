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
| Models — `FriendlyMatch`, `PerformanceHighlight`           | ⬜ Pendente |
| Services — `FriendlyMatchService`, `MatchResultService`    | ⬜ Pendente |
| Job — `ExpireFriendlyMatchInvitations`                     | ⬜ Pendente |
| Notifications (5)                                          | ⬜ Pendente |
| Form Requests (4)                                          | ⬜ Pendente |
| API Resources (2)                                          | ⬜ Pendente |
| Policy — `FriendlyMatchPolicy`                             | ⬜ Pendente |
| Controllers (3)                                            | ⬜ Pendente |
| Rotas API (`routes/api.php`)                               | ⬜ Pendente |
| Types TypeScript                                           | ⬜ Pendente |
| Factories (2)                                              | ⬜ Pendente |
| Testes Feature (4 classes)                                 | ⬜ Pendente |

### Progresso atual

Primeiro bloco da Fase 2 já implementado:

- migrations de amistosos
- migration de estatísticas individuais do amistoso
- enums de confirmação, estado da partida e confirmação de resultado

Próximo bloco recomendado:

- models
- services

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

Localização: `app/Http/Resources/`

### `FriendlyMatchResource`

```php
class FriendlyMatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'home_team'         => TeamSportModeResource::make($this->whenLoaded('homeTeam')),
            'away_team'         => TeamSportModeResource::make($this->whenLoaded('awayTeam')),
            'scheduled_at'      => $this->scheduled_at,
            'location'          => $this->location,
            'confirmation'      => $this->confirmation,
            'invite_expires_at' => $this->invite_expires_at,
            'match_status'      => $this->match_status,
            'home_goals'        => $this->home_goals,
            'away_goals'        => $this->away_goals,
            'home_notes'        => $this->when($this->isParticipant($request->user()), $this->home_notes),
            'away_notes'        => $this->when($this->isParticipant($request->user()), $this->away_notes),
            'is_public'         => $this->is_public,
            'result_status'     => $this->result_status,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }

    private function isParticipant(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->id, [
            $this->homeTeam?->team?->owner_id,
            $this->awayTeam?->team?->owner_id,
        ]);
    }
}
```

> `home_notes` e `away_notes` são visíveis apenas para os donos dos dois times envolvidos.

### `PerformanceHighlightResource`

```php
class PerformanceHighlightResource extends JsonResource
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
        ];
    }
}
```

---

## 11. Policy

Localização: `app/Policies/FriendlyMatchPolicy.php`

```php
class FriendlyMatchPolicy
{
    /** Dono do time desafiante pode remover convite pendente */
    public function delete(User $user, FriendlyMatch $match): bool
    {
        return $user->id === $match->homeTeam->team->owner_id
            && $match->isPending();
    }

    /** Apenas o dono do time desafiado confirma ou recusa */
    public function respond(User $user, FriendlyMatch $match): bool
    {
        return $user->id === $match->awayTeam->team->owner_id;
    }

    /** Qualquer dos dois donos pode cancelar ou adiar */
    public function manage(User $user, FriendlyMatch $match): bool
    {
        return in_array($user->id, [
            $match->homeTeam->team->owner_id,
            $match->awayTeam->team->owner_id,
        ]);
    }

    /** Qualquer dos dois donos pode registrar ou interagir com o resultado */
    public function manageResult(User $user, FriendlyMatch $match): bool
    {
        return $this->manage($user, $match);
    }

    /** Cada dono registra highlights apenas dos seus próprios jogadores */
    public function manageHighlights(User $user, FriendlyMatch $match): bool
    {
        return $this->manage($user, $match);
    }
}
```

> Registrar em `app/Providers/AppServiceProvider.php` via `Gate::policy(FriendlyMatch::class, FriendlyMatchPolicy::class)`.

---

## 12. API Controllers

Localização: `app/Http/Controllers/Api/`

Todos estendem `BaseController` e requerem `auth:sanctum`.

### 12.1 `FriendlyMatchController`

```
GET    /api/v1/friendly-matches                  → index
POST   /api/v1/friendly-matches                  → store  (desafiante cria)
GET    /api/v1/friendly-matches/{match}           → show
DELETE /api/v1/friendly-matches/{match}           → destroy (remove convite pendente)
POST   /api/v1/friendly-matches/{match}/confirm  → confirm (desafiado confirma)
POST   /api/v1/friendly-matches/{match}/reject   → reject  (desafiado recusa)
POST   /api/v1/friendly-matches/{match}/cancel   → cancel  (qualquer dono)
POST   /api/v1/friendly-matches/{match}/postpone → postpone (qualquer dono)
```

```php
class FriendlyMatchController extends BaseController
{
    public function __construct(private FriendlyMatchService $matchService) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $matches = FriendlyMatch::where(function ($q) use ($userId) {
            $q->whereHas('homeTeam.team', fn ($q) => $q->where('owner_id', $userId))
              ->orWhereHas('awayTeam.team', fn ($q) => $q->where('owner_id', $userId));
        })
        ->with(['homeTeam.team', 'homeTeam.sportMode', 'awayTeam.team', 'awayTeam.sportMode'])
        ->latest()
        ->get();

        return $this->sendResponse(FriendlyMatchResource::collection($matches), 'Matches retrieved.');
    }

    public function store(StoreFriendlyMatchRequest $request): JsonResponse
    {
        // Garante que home_team_id pertence ao usuário autenticado
        $homeTeam = TeamSportMode::where('id', $request->integer('home_team_id'))
            ->whereHas('team', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->firstOrFail();

        $match = $this->matchService->create(
            array_merge($request->validated(), ['home_team_id' => $homeTeam->id]),
            $request->user(),
        );

        $match->awayTeam->team->owner->notify(new FriendlyMatchInvitedNotification($match));

        return $this->sendResponse(new FriendlyMatchResource($match), 'Amistoso criado.', 201);
    }

    public function show(FriendlyMatch $match): JsonResponse
    {
        $match->load(['homeTeam.team', 'homeTeam.sportMode', 'awayTeam.team', 'awayTeam.sportMode']);

        return $this->sendResponse(new FriendlyMatchResource($match), 'Match retrieved.');
    }

    public function destroy(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('delete', $match);
        $this->matchService->removePendingInvite($match);

        return $this->sendResponse([], 'Convite removido.');
    }

    public function confirm(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('respond', $match);
        $updated = $this->matchService->confirm($match);

        $match->homeTeam->team->owner->notify(new FriendlyMatchConfirmedNotification($updated));

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Amistoso confirmado.');
    }

    public function reject(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('respond', $match);
        $updated = $this->matchService->reject($match);

        $match->homeTeam->team->owner->notify(new FriendlyMatchRejectedNotification($updated));

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Amistoso recusado.');
    }

    public function cancel(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manage', $match);
        $updated = $this->matchService->cancel($match);

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Amistoso cancelado.');
    }

    public function postpone(PostponeFriendlyMatchRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manage', $match);
        $updated = $this->matchService->postpone($match, $request->validated());

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Amistoso adiado.');
    }
}
```

### 12.2 `MatchResultController`

```
POST   /api/v1/friendly-matches/{match}/result         → store   (qualquer dono registra placar)
POST   /api/v1/friendly-matches/{match}/result/confirm → confirm (o outro time confirma)
POST   /api/v1/friendly-matches/{match}/result/dispute → dispute (o outro time contesta)
```

```php
class MatchResultController extends BaseController
{
    public function __construct(private MatchResultService $resultService) {}

    public function store(RegisterMatchResultRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_status === ResultStatus::Pending) {
            return $this->sendError('Resultado já registrado e aguardando confirmação.', [], 409);
        }

        $updated = $this->resultService->register($match, $request->validated(), $request->user());

        $this->resolveOtherOwner($match, $request->user())
             ->notify(new MatchResultRegisteredNotification($updated));

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Resultado registrado.');
    }

    public function confirm(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_registered_by === $request->user()->id) {
            return $this->sendError('O registrador não pode confirmar o próprio resultado.', [], 403);
        }

        $updated = $this->resultService->confirmResult($match);

        $match->resultRegisteredBy->notify(new MatchResultConfirmedNotification($updated));

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Resultado confirmado. Amistoso encerrado.');
    }

    public function dispute(Request $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageResult', $match);

        if ($match->result_registered_by === $request->user()->id) {
            return $this->sendError('O registrador não pode contestar o próprio resultado.', [], 403);
        }

        $updated = $this->resultService->disputeResult($match);

        return $this->sendResponse(new FriendlyMatchResource($updated), 'Resultado contestado. Registre novamente.');
    }

    private function resolveOtherOwner(FriendlyMatch $match, User $current): User
    {
        return $current->id === $match->homeTeam->team->owner_id
            ? $match->awayTeam->team->owner
            : $match->homeTeam->team->owner;
    }
}
```

### 12.3 `PerformanceHighlightController`

```
GET    /api/v1/friendly-matches/{match}/highlights → index
POST   /api/v1/friendly-matches/{match}/highlights → store (bulk — cada dono registra os seus)
```

```php
class PerformanceHighlightController extends BaseController
{
    public function __construct(private MatchResultService $resultService) {}

    public function index(FriendlyMatch $match): JsonResponse
    {
        $highlights = $match->highlights()->with('playerMembership.player.user')->get();

        return $this->sendResponse(PerformanceHighlightResource::collection($highlights), 'Highlights retrieved.');
    }

    public function store(StorePerformanceHighlightRequest $request, FriendlyMatch $match): JsonResponse
    {
        $this->authorize('manageHighlights', $match);

        $userId = $request->user()->id;

        // Coleta IDs de player_memberships do time do usuário autenticado neste amistoso
        $allowedIds = PlayerMembership::whereIn('team_sport_mode_id', [$match->home_team_id, $match->away_team_id])
            ->whereHas('teamSportMode.team', fn ($q) => $q->where('owner_id', $userId))
            ->pluck('id')
            ->toArray();

        $results = [];
        foreach ($request->validated()['highlights'] as $item) {
            if (!in_array($item['player_membership_id'], $allowedIds)) {
                return $this->sendError(
                    "player_membership_id {$item['player_membership_id']} não pertence ao seu time.",
                    [],
                    403,
                );
            }
            $results[] = $this->resultService->registerHighlight($match, $item);
        }

        return $this->sendResponse(
            PerformanceHighlightResource::collection(collect($results)),
            'Estatísticas registradas.',
        );
    }
}
```

---

## 13. Rotas

`routes/api.php` — adicionado ao grupo `auth:sanctum` existente:

```php
// Amistosos
Route::prefix('v1/friendly-matches')->name('api.friendly-matches.')->group(function () {
    Route::get('/',                        [Api\FriendlyMatchController::class, 'index'])->name('index');
    Route::post('/',                       [Api\FriendlyMatchController::class, 'store'])->name('store');
    Route::get('/{match}',                 [Api\FriendlyMatchController::class, 'show'])->name('show');
    Route::delete('/{match}',              [Api\FriendlyMatchController::class, 'destroy'])->name('destroy');
    Route::post('/{match}/confirm',        [Api\FriendlyMatchController::class, 'confirm'])->name('confirm');
    Route::post('/{match}/reject',         [Api\FriendlyMatchController::class, 'reject'])->name('reject');
    Route::post('/{match}/cancel',         [Api\FriendlyMatchController::class, 'cancel'])->name('cancel');
    Route::post('/{match}/postpone',       [Api\FriendlyMatchController::class, 'postpone'])->name('postpone');

    // Resultado (confirmação bilateral)
    Route::post('/{match}/result',         [Api\MatchResultController::class, 'store'])->name('result.store');
    Route::post('/{match}/result/confirm', [Api\MatchResultController::class, 'confirm'])->name('result.confirm');
    Route::post('/{match}/result/dispute', [Api\MatchResultController::class, 'dispute'])->name('result.dispute');

    // Estatísticas individuais
    Route::get('/{match}/highlights',      [Api\PerformanceHighlightController::class, 'index'])->name('highlights.index');
    Route::post('/{match}/highlights',     [Api\PerformanceHighlightController::class, 'store'])->name('highlights.store');
});
```

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

```
database/factories/
├── FriendlyMatchFactory.php
└── PerformanceHighlightFactory.php
```

### `FriendlyMatchFactory`

```php
public function definition(): array
{
    return [
        'home_team_id'         => TeamSportMode::factory(),
        'away_team_id'         => TeamSportMode::factory(),
        'scheduled_at'         => now()->addDays(7),
        'location'             => fake()->address(),
        'confirmation'         => MatchConfirmation::Pending,
        'invite_expires_at'    => now()->addDays(2),
        'match_status'         => null,
        'home_goals'           => null,
        'away_goals'           => null,
        'is_public'            => false,
        'result_status'        => ResultStatus::None,
        'result_registered_by' => null,
    ];
}

public function pending(): static
{
    return $this->state(['confirmation' => MatchConfirmation::Pending, 'match_status' => null]);
}

public function scheduled(): static
{
    return $this->state([
        'confirmation' => MatchConfirmation::Confirmed,
        'match_status' => MatchStatus::Scheduled,
    ]);
}

public function completed(): static
{
    return $this->state([
        'confirmation'  => MatchConfirmation::Confirmed,
        'match_status'  => MatchStatus::Completed,
        'result_status' => ResultStatus::Confirmed,
        'home_goals'    => fake()->numberBetween(0, 5),
        'away_goals'    => fake()->numberBetween(0, 5),
    ]);
}

public function withPendingResult(User $registeredBy): static
{
    return $this->state([
        'confirmation'         => MatchConfirmation::Confirmed,
        'match_status'         => MatchStatus::Scheduled,
        'result_status'        => ResultStatus::Pending,
        'home_goals'           => 2,
        'away_goals'           => 1,
        'result_registered_by' => $registeredBy->id,
    ]);
}
```

### `PerformanceHighlightFactory`

```php
public function definition(): array
{
    return [
        'friendly_match_id'    => FriendlyMatch::factory()->completed(),
        'player_membership_id' => PlayerMembership::factory(),
        'goals'                => fake()->numberBetween(0, 3),
        'assists'              => fake()->numberBetween(0, 3),
        'yellow_cards'         => fake()->numberBetween(0, 1),
        'red_cards'            => 0,
    ];
}
```

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

- [ ] Factories: `FriendlyMatchFactory`, `PerformanceHighlightFactory`
- [ ] `FriendlyMatchTest`
- [ ] `MatchResultTest`
- [ ] `PerformanceHighlightTest`
- [ ] `ExpireFriendlyMatchInvitationsTest`
- [ ] Todos os testes passando (`php artisan test`)

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
