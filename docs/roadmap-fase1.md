# Roadmap Fase 1 — Identidade, Times e Elenco

> Detalhamento completo de implementação da Fase 1. Cobertura: migrations, seeders, models, services, form requests, resources, controllers, rotas, types TypeScript e testes.
>
> **Importante:** A partir da Fase 1, a entrega é exclusivamente via API (`auth:sanctum`). O painel administrativo de acompanhamento será implementado ao final de todas as fases, reutilizando os services já existentes.
>
> Referências de schema: `docs/database/schema.md` §1 e §3.
> Referências de padrões: `docs/patterns/`.

---

## 1. Escopo

| Item                                     | Status      |
| ---------------------------------------- | ----------- |
| Migrations — `players`, `staff_members`  | ✅ Concluído |
| Migrations — `teams`, `team_sport_modes` | ✅ Concluído |
| Migration — `team_staff`                 | ✅ Concluído |
| Migration — `player_memberships`         | ✅ Concluído |
| Migration — `team_invitations`           | ✅ Concluído |
| Models (7)                               | ✅ Concluído |
| Enums — `InvitationStatus`, `UserRole`   | ✅ Concluído |
| Services                                 | ✅ Concluído |
| Form Requests                            | ⬜ Pendente |
| API Resources                            | ⬜ Pendente |
| API Controllers                          | ⬜ Pendente |
| Rotas API (`routes/api.php`)             | ⬜ Pendente |
| Types TypeScript                         | ⬜ Pendente |
| Testes Feature                           | ⬜ Pendente |

### Progresso atual

Primeiro bloco da Fase 1 já implementado:

- migrations de identidade esportiva
- migrations de times e vínculos
- migration de convites
- enum `InvitationStatus`
- enum `UserRole`
- models do contexto de identidade, times e elenco
- services do contexto de identidade, times, elenco e convites

Próximo bloco recomendado:

- requests/resources/controllers da API

---

## 2. Contexto de Domínio

```
users (Fase 0 base)
 ├── players          → perfil esportivo opcional
 └── staff_members    → membro de comissão técnica opcional

teams
 ├── owner_id → users
 ├── team_sport_modes    → (team_id, sport_mode_id)
 │    ├── player_memberships   → vínculos aceitos de jogadores
 │    └── team_invitations     → convites pendentes
 └── team_staff          → comissão técnica vinculada ao time
```

Um `user` pode acumular todos os papéis simultaneamente: ter `players`, criar `teams`, e ter `staff_members`. Esse é o cenário mais comum no futebol amador.

---

## 3. Migrations

### 3.1 `create_players_table`

```php
Schema::create('players', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id')->primary();
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->string('cpf', 11)->unique()->nullable();
    $table->string('rg', 20)->nullable();
    $table->date('birth_date')->nullable();
    $table->string('phone', 15)->nullable();
    $table->boolean('is_discoverable')->default(false);
    $table->boolean('history_public')->default(false);
    $table->string('city', 100)->nullable();
    $table->string('state', 60)->nullable();
    $table->char('country', 2)->nullable();
    $table->timestamps();
});
```

### 3.2 `create_staff_members_table`

```php
Schema::create('staff_members', function (Blueprint $table) {
    $table->unsignedBigInteger('user_id')->primary();
    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
    $table->foreignId('staff_role_id')->constrained()->restrictOnDelete();
    $table->timestamps();
});
```

### 3.3 `create_teams_table`

```php
Schema::create('teams', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
    $table->string('name', 45);
    $table->string('description', 255)->nullable();
    $table->string('badge', 100)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### 3.4 `create_team_sport_modes_table`

```php
Schema::create('team_sport_modes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sport_mode_id')->constrained()->restrictOnDelete();
    $table->unique(['team_id', 'sport_mode_id']);
    $table->timestamps();
});
```

### 3.5 `create_team_staff_table`

```php
Schema::create('team_staff', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_id')->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('staff_member_id');
    $table->foreign('staff_member_id')->references('user_id')->on('staff_members')->cascadeOnDelete();
    $table->unique(['team_id', 'staff_member_id']);
    $table->timestamps();
});
```

### 3.6 `create_player_memberships_table`

```php
Schema::create('player_memberships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_sport_mode_id')->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('player_id');
    $table->foreign('player_id')->references('user_id')->on('players')->cascadeOnDelete();
    $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
    $table->boolean('is_starter')->default(false);
    $table->timestamp('left_at')->nullable();
    $table->unique(['team_sport_mode_id', 'player_id', 'left_at']);
    $table->timestamps();
});
```

> A unicidade `(team_sport_mode_id, player_id, left_at)` permite que um jogador retorne a um mesmo time após ter saído (`left_at` preenchido). `left_at = null` significa ativo.

### 3.7 `create_team_invitations_table`

```php
Schema::create('team_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('team_sport_mode_id')->constrained()->cascadeOnDelete();
    $table->foreignId('invited_user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('invited_by')->constrained('users')->restrictOnDelete();
    $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
    $table->timestamp('expires_at')->nullable();
    $table->string('message', 255)->nullable();
    $table->timestamps();
});
```

---

## 4. Enums

### `app/Enums/InvitationStatus.php`

```php
enum InvitationStatus: string
{
    case Pending  = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired  = 'expired';
}
```

---

## 5. Models

### 5.1 `Player`

```php
class Player extends Model
{
    protected $table = 'players';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'cpf', 'rg', 'birth_date', 'phone',
        'is_discoverable', 'history_public', 'city', 'state', 'country',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'     => 'date',
            'is_discoverable' => 'boolean',
            'history_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class, 'player_id', 'user_id');
    }

    public function activeMemberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class, 'player_id', 'user_id')
                    ->whereNull('left_at');
    }
}
```

### 5.2 `StaffMember`

```php
class StaffMember extends Model
{
    protected $table = 'staff_members';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'staff_role_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class, 'staff_role_id');
    }

    public function teamStaff(): HasMany
    {
        return $this->hasMany(TeamStaff::class, 'staff_member_id', 'user_id');
    }
}
```

### 5.3 `Team`

```php
class Team extends Model
{
    protected $table = 'teams';

    protected $fillable = ['owner_id', 'name', 'description', 'badge', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sportModes(): HasMany
    {
        return $this->hasMany(TeamSportMode::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(TeamStaff::class);
    }
}
```

### 5.4 `TeamSportMode`

```php
class TeamSportMode extends Model
{
    protected $table = 'team_sport_modes';

    protected $fillable = ['team_id', 'sport_mode_id'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function sportMode(): BelongsTo
    {
        return $this->belongsTo(SportMode::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class);
    }

    public function activeMemberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class)->whereNull('left_at');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }
}
```

### 5.5 `TeamStaff`

```php
class TeamStaff extends Model
{
    protected $table = 'team_staff';

    protected $fillable = ['team_id', 'staff_member_id'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class, 'staff_member_id', 'user_id');
    }
}
```

### 5.6 `PlayerMembership`

```php
class PlayerMembership extends Model
{
    protected $table = 'player_memberships';

    protected $fillable = ['team_sport_mode_id', 'player_id', 'position_id', 'is_starter', 'left_at'];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'left_at'    => 'datetime',
        ];
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'user_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
```

### 5.7 `TeamInvitation`

```php
class TeamInvitation extends Model
{
    protected $table = 'team_invitations';

    protected $fillable = [
        'team_sport_mode_id', 'invited_user_id', 'invited_by',
        'position_id', 'status', 'expires_at', 'message',
    ];

    protected function casts(): array
    {
        return [
            'status'     => InvitationStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function teamSportMode(): BelongsTo
    {
        return $this->belongsTo(TeamSportMode::class);
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending;
    }
}
```

---

## 6. Services

Localização: `app/Services/`

| Service                 | Arquivo                                       |
| ----------------------- | --------------------------------------------- |
| `PlayerService`         | `app/Services/Player/PlayerService.php`       |
| `StaffMemberService`    | `app/Services/Staff/StaffMemberService.php`   |
| `TeamService`           | `app/Services/Team/TeamService.php`           |
| `TeamRosterService`     | `app/Services/Team/TeamRosterService.php`     |
| `TeamInvitationService` | `app/Services/Team/TeamInvitationService.php` |

### 6.1 `PlayerService`

```php
class PlayerService
{
    public function createProfile(array $data, User $user): Player
    {
        return Player::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function updateProfile(Player $player, array $data): Player
    {
        $player->update($data);
        return $player->fresh();
    }
}
```

### 6.2 `StaffMemberService`

```php
class StaffMemberService
{
    public function createProfile(array $data, User $user): StaffMember
    {
        return StaffMember::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function updateProfile(StaffMember $staffMember, array $data): StaffMember
    {
        $staffMember->update($data);
        return $staffMember->fresh();
    }
}
```

### 6.3 `TeamService`

```php
class TeamService
{
    public function create(array $data, User $owner): Team
    {
        // TODO Fase 4: verificar limite de plano Free (máx 1 time ativo)
        return DB::transaction(function () use ($data, $owner) {
            $team = Team::create(array_merge($data, ['owner_id' => $owner->id]));

            // Vincula as modalidades informadas na criação
            if (!empty($data['sport_mode_ids'])) {
                foreach ($data['sport_mode_ids'] as $sportModeId) {
                    $team->sportModes()->create(['sport_mode_id' => $sportModeId]);
                }
            }

            return $team->load('sportModes.sportMode');
        });
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function deactivate(Team $team): void
    {
        $team->update(['is_active' => false]);
    }

    public function addSportMode(Team $team, int $sportModeId): TeamSportMode
    {
        return $team->sportModes()->firstOrCreate(['sport_mode_id' => $sportModeId]);
    }

    public function removeSportMode(TeamSportMode $teamSportMode): void
    {
        // Não excluir se houver membros ativos
        if ($teamSportMode->activeMemberships()->exists()) {
            throw new \DomainException('Não é possível remover modalidade com jogadores ativos.');
        }
        $teamSportMode->delete();
    }
}
```

### 6.4 `TeamRosterService`

```php
class TeamRosterService
{
    public function removeMember(PlayerMembership $membership): void
    {
        $membership->update(['left_at' => now()]);
    }

    /**
     * O próprio jogador sai voluntariamente do elenco.
     * Autorização: verificar que $request->user()->id === $membership->player_id no controller.
     */
    public function leaveTeam(PlayerMembership $membership): void
    {
        $membership->update(['left_at' => now()]);
    }

    public function acceptInvitation(TeamInvitation $invitation): PlayerMembership
    {
        return DB::transaction(function () use ($invitation) {
            $invitation->update(['status' => InvitationStatus::Accepted]);

            // Garante que o usuário tem perfil de jogador
            $player = Player::firstOrCreate(
                ['user_id' => $invitation->invited_user_id],
            );

            return PlayerMembership::create([
                'team_sport_mode_id' => $invitation->team_sport_mode_id,
                'player_id'          => $invitation->invited_user_id,
                'position_id'        => $invitation->position_id,
                'is_starter'         => false,
            ]);
        });
    }
}
```

### 6.5 `TeamInvitationService`

```php
class TeamInvitationService
{
    public function send(TeamSportMode $teamSportMode, array $data, User $sender): TeamInvitation
    {
        // Verifica se usuário já é membro ativo
        if ($teamSportMode->activeMemberships()->where('player_id', $data['invited_user_id'])->exists()) {
            throw new \DomainException('Usuário já é membro ativo desta equipe.');
        }

        // Cancela convite pendente anterior (se houver)
        $teamSportMode->invitations()
            ->where('invited_user_id', $data['invited_user_id'])
            ->where('status', InvitationStatus::Pending)
            ->update(['status' => InvitationStatus::Expired]);

        return TeamInvitation::create(array_merge($data, [
            'team_sport_mode_id' => $teamSportMode->id,
            'invited_by'         => $sender->id,
            'status'             => InvitationStatus::Pending,
            'expires_at'         => now()->addDays(7),
        ]));
    }

    public function reject(TeamInvitation $invitation): void
    {
        $invitation->update(['status' => InvitationStatus::Rejected]);
    }
}
```

---

### 6.6 Job: `ExpireTeamInvitations`

Localização: `app/Jobs/ExpireTeamInvitations.php`

Executado pelo scheduler (horário) para expirar convites vencidos. O campo `expires_at` já existe na migração `team_invitations`.

```php
class ExpireTeamInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        TeamInvitation::where('status', InvitationStatus::Pending)
            ->where('expires_at', '<', now())
            ->update(['status' => InvitationStatus::Expired]);
    }
}
```

Registrar em `routes/console.php`:

```php
Schedule::job(new ExpireTeamInvitations)->hourly();
```

---

## 7. Form Requests

Localização: `app/Http/Requests/`

### Players

#### `StorePlayerRequest`

```php
public function rules(): array
{
    return [
        'cpf'            => ['nullable', 'string', 'size:11', Rule::unique('players')->ignore($this->user()->id, 'user_id')],
        'rg'             => ['nullable', 'string', 'max:20'],
        'birth_date'     => ['nullable', 'date', 'before:today'],
        'phone'          => ['nullable', 'string', 'max:15'],
        'is_discoverable' => ['boolean'],
        'history_public' => ['boolean'],
        'city'           => ['nullable', 'string', 'max:100'],
        'state'          => ['nullable', 'string', 'max:60'],
        'country'        => ['nullable', 'string', 'size:2'],
    ];
}
```

#### `UpdatePlayerRequest` — mesmas regras, sem restrição de unicidade extra (CPF)

### Teams

#### `StoreTeamRequest`

```php
public function rules(): array
{
    return [
        'name'           => ['required', 'string', 'max:45'],
        'description'    => ['nullable', 'string', 'max:255'],
        'sport_mode_ids' => ['required', 'array', 'min:1'],
        'sport_mode_ids.*' => ['integer', 'exists:sport_modes,id'],
    ];
}
```

#### `UpdateTeamRequest`

```php
'name'        => ['required', 'string', 'max:45'],
'description' => ['nullable', 'string', 'max:255'],
```

#### `StoreTeamInvitationRequest`

```php
public function rules(): array
{
    return [
        'invited_user_id' => ['required', 'integer', 'exists:users,id'],
        'position_id'     => ['nullable', 'integer', 'exists:positions,id'],
        'message'         => ['nullable', 'string', 'max:255'],
    ];
}
```

#### `StoreStaffMemberRequest`

```php
'staff_role_id' => ['required', 'integer', 'exists:staff_roles,id'],
```

---

## 8. API Resources

Localização: `app/Http/Resources/`

### `PlayerResource`

```php
class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id'        => $this->user_id,
            'name'           => $this->user?->name,
            'avatar'         => $this->user?->avatar,
            'cpf'            => $this->when($request->user()?->id === $this->user_id || $request->user()?->isAdmin(), $this->cpf),
            'birth_date'     => $this->birth_date?->toDateString(),
            'phone'          => $this->when($request->user()?->id === $this->user_id || $request->user()?->isAdmin(), $this->phone),
            'is_discoverable' => $this->is_discoverable,
            'history_public' => $this->history_public,
            'city'           => $this->city,
            'state'          => $this->state,
            'country'        => $this->country,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
```

> `cpf` e `phone` são expostos apenas ao próprio jogador ou ao `admin` (verificação por `user_id` ou `isAdmin()`).

### `TeamResource`

```php
class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'badge'       => $this->badge,
            'is_active'   => $this->is_active,
            'owner'       => UserMinimalResource::make($this->whenLoaded('owner')),
            'sport_modes' => TeamSportModeResource::collection($this->whenLoaded('sportModes')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
```

### `TeamSportModeResource`

```php
return [
    'id'          => $this->id,
    'sport_mode'  => SportModeResource::make($this->whenLoaded('sportMode')),
    'member_count' => $this->activeMemberships()->count(),
];
```

### `PlayerMembershipResource`

```php
return [
    'id'         => $this->id,
    'player'     => PlayerResource::make($this->whenLoaded('player')),
    'position'   => PositionResource::make($this->whenLoaded('position')),
    'is_starter' => $this->is_starter,
    'left_at'    => $this->left_at,
    'joined_at'  => $this->created_at,
];
```

### `TeamInvitationResource`

```php
return [
    'id'              => $this->id,
    'team_sport_mode' => TeamSportModeResource::make($this->whenLoaded('teamSportMode')),
    'invited_user'    => UserMinimalResource::make($this->whenLoaded('invitedUser')),
    'position'        => PositionResource::make($this->whenLoaded('position')),
    'status'          => $this->status,
    'expires_at'      => $this->expires_at,
    'message'         => $this->message,
    'created_at'      => $this->created_at,
];
```

### `UserMinimalResource`

```php
return [
    'id'     => $this->id,
    'name'   => $this->name,
    'avatar' => $this->avatar,
];
```

---

## 9. API Controllers

Localização: `app/Http/Controllers/Api/`

Todos estendem `BaseController` e requerem `auth:sanctum`.

### 9.1 `PlayerController`

```
GET    /api/v1/players/{player}     → show
POST   /api/v1/players              → store  (cria perfil do usuário autenticado)
PUT    /api/v1/players              → update (atualiza perfil do usuário autenticado)
```

```php
class PlayerController extends BaseController
{
    public function __construct(private PlayerService $playerService) {}

    public function show(Player $player): JsonResponse
    {
        return $this->sendResponse(new PlayerResource($player->load('user')), 'Player retrieved.');
    }

    public function store(StorePlayerRequest $request): JsonResponse
    {
        if ($request->user()->player) {
            return $this->sendError('Perfil de jogador já existe.', [], 409);
        }
        $player = $this->playerService->createProfile($request->validated(), $request->user());
        return $this->sendResponse(new PlayerResource($player), 'Perfil de jogador criado.', 201);
    }

    public function update(UpdatePlayerRequest $request): JsonResponse
    {
        $player = $request->user()->player;
        if (!$player) {
            return $this->sendError('Perfil de jogador não encontrado.', [], 404);
        }
        $player = $this->playerService->updateProfile($player, $request->validated());
        return $this->sendResponse(new PlayerResource($player), 'Perfil atualizado.');
    }
}
```

### 9.2 `StaffMemberController`

```
POST   /api/v1/staff-members        → store  (cria perfil do usuário autenticado)
PUT    /api/v1/staff-members        → update (atualiza perfil do usuário autenticado)
```

### 9.3 `TeamController`

```
GET    /api/v1/teams                → index  (times do usuário autenticado)
POST   /api/v1/teams                → store
GET    /api/v1/teams/{team}         → show
PUT    /api/v1/teams/{team}         → update (somente owner)
DELETE /api/v1/teams/{team}         → destroy → desativa (is_active = false)
```

```php
class TeamController extends BaseController
{
    public function __construct(private TeamService $teamService) {}

    public function index(Request $request): JsonResponse
    {
        $teams = Team::where('owner_id', $request->user()->id)
                     ->with('sportModes.sportMode')
                     ->get();
        return $this->sendResponse(TeamResource::collection($teams), 'Teams retrieved.');
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->create($request->validated(), $request->user());
        return $this->sendResponse(new TeamResource($team), 'Time criado.', 201);
    }

    public function show(Team $team): JsonResponse
    {
        $team->load(['owner', 'sportModes.sportMode']);
        return $this->sendResponse(new TeamResource($team), 'Team retrieved.');
    }

    public function update(UpdateTeamRequest $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team); // TeamPolicy
        $team = $this->teamService->update($team, $request->validated());
        return $this->sendResponse(new TeamResource($team), 'Time atualizado.');
    }

    public function destroy(Request $request, Team $team): JsonResponse
    {
        $this->authorize('delete', $team); // TeamPolicy
        $this->teamService->deactivate($team);
        return $this->sendResponse([], 'Time desativado.');
    }
}
```

### 9.4 `TeamSportModeController`

```
POST   /api/v1/teams/{team}/sport-modes              → store  (adiciona modalidade)
DELETE /api/v1/teams/{team}/sport-modes/{teamSportMode} → destroy (remove modalidade)
```

### 9.5 `TeamRosterController`

```
GET    /api/v1/teams/{team}/sport-modes/{teamSportMode}/members        → index
DELETE /api/v1/teams/{team}/sport-modes/{teamSportMode}/members/{membership} → destroy (remove jogador)
```

```php
class TeamRosterController extends BaseController
{
    public function __construct(private TeamRosterService $rosterService) {}

    public function index(Team $team, TeamSportMode $teamSportMode): JsonResponse
    {
        $members = $teamSportMode->activeMemberships()
                                 ->with(['player.user', 'position'])
                                 ->get();
        return $this->sendResponse(PlayerMembershipResource::collection($members), 'Roster retrieved.');
    }

    public function destroy(Team $team, TeamSportMode $teamSportMode, PlayerMembership $membership): JsonResponse
    {
        $this->authorize('manageRoster', $team); // TeamPolicy
        $this->rosterService->removeMember($membership);
        return $this->sendResponse([], 'Jogador removido do elenco.');
    }

    /**
     * O próprio jogador sai do elenco voluntariamente.
     * Não usa policy: valida diretamente que a membership pertence ao usuário autenticado.
     */
    public function leave(Request $request, Team $team, TeamSportMode $teamSportMode, PlayerMembership $membership): JsonResponse
    {
        if ($membership->player_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }
        $this->rosterService->leaveTeam($membership);
        return $this->sendResponse([], 'Você saiu do elenco.');
    }
}
```

### 9.6 `TeamInvitationController`

```
POST   /api/v1/teams/{team}/sport-modes/{teamSportMode}/invitations → store (owner envia convite)
GET    /api/v1/invitations                                          → index (convites que recebi)
POST   /api/v1/invitations/{invitation}/accept                      → accept
POST   /api/v1/invitations/{invitation}/reject                      → reject
```

```php
class TeamInvitationController extends BaseController
{
    public function __construct(
        private TeamInvitationService $invitationService,
        private TeamRosterService $rosterService,
    ) {}

    public function store(StoreTeamInvitationRequest $request, Team $team, TeamSportMode $teamSportMode): JsonResponse
    {
        $this->authorize('manageRoster', $team);
        $invitation = $this->invitationService->send($teamSportMode, $request->validated(), $request->user());
        return $this->sendResponse(new TeamInvitationResource($invitation), 'Convite enviado.', 201);
    }

    public function index(Request $request): JsonResponse
    {
        $invitations = TeamInvitation::where('invited_user_id', $request->user()->id)
                                     ->where('status', InvitationStatus::Pending)
                                     ->with(['teamSportMode.team', 'teamSportMode.sportMode', 'position'])
                                     ->get();
        return $this->sendResponse(TeamInvitationResource::collection($invitations), 'Invitations retrieved.');
    }

    public function accept(Request $request, TeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invited_user_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }
        if (!$invitation->isPending()) {
            return $this->sendError('Convite não está mais pendente.', [], 409);
        }
        $membership = $this->rosterService->acceptInvitation($invitation);
        return $this->sendResponse(new PlayerMembershipResource($membership), 'Convite aceito.');
    }

    public function reject(Request $request, TeamInvitation $invitation): JsonResponse
    {
        if ($invitation->invited_user_id !== $request->user()->id) {
            return $this->sendError('Não autorizado.', [], 403);
        }
        if (!$invitation->isPending()) {
            return $this->sendError('Convite não está mais pendente.', [], 409);
        }
        $this->invitationService->reject($invitation);
        return $this->sendResponse([], 'Convite recusado.');
    }
}
```

---

## 10. Policies

Localização: `app/Policies/TeamPolicy.php`

```php
class TeamPolicy
{
    public function update(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }

    public function manageRoster(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id || $user->isAdmin();
    }
}
```

> Registrar em `app/Providers/AppServiceProvider.php` via `Gate::policy(Team::class, TeamPolicy::class)`.

---

## 11. Rotas

`routes/api.php` — tudo sob `auth:sanctum`:

```php
Route::middleware('auth:sanctum')->group(function () {

    // Perfis de usuário
    Route::post('v1/players',         [Api\PlayerController::class, 'store']);
    Route::put('v1/players',          [Api\PlayerController::class, 'update']);
    Route::get('v1/players/{player}', [Api\PlayerController::class, 'show']);

    Route::post('v1/staff-members',   [Api\StaffMemberController::class, 'store']);
    Route::put('v1/staff-members',    [Api\StaffMemberController::class, 'update']);

    // Times
    Route::apiResource('v1/teams', Api\TeamController::class);

    // Modalidades de um time
    Route::prefix('v1/teams/{team}/sport-modes')->name('api.teams.sport-modes.')->group(function () {
        Route::post('/',                               [Api\TeamSportModeController::class, 'store'])->name('store');
        Route::delete('/{teamSportMode}',              [Api\TeamSportModeController::class, 'destroy'])->name('destroy');

        // Elenco por modalidade
        Route::get('/{teamSportMode}/members',                            [Api\TeamRosterController::class, 'index'])->name('members.index');
        Route::delete('/{teamSportMode}/members/{membership}',            [Api\TeamRosterController::class, 'destroy'])->name('members.destroy');
        Route::delete('/{teamSportMode}/members/{membership}/leave',      [Api\TeamRosterController::class, 'leave'])->name('members.leave');

        // Convites
        Route::post('/{teamSportMode}/invitations',    [Api\TeamInvitationController::class, 'store'])->name('invitations.store');
    });

    // Convites recebidos (inbox do usuário)
    Route::get('v1/invitations',                       [Api\TeamInvitationController::class, 'index'])->name('api.invitations.index');
    Route::post('v1/invitations/{invitation}/accept',  [Api\TeamInvitationController::class, 'accept'])->name('api.invitations.accept');
    Route::post('v1/invitations/{invitation}/reject',  [Api\TeamInvitationController::class, 'reject'])->name('api.invitations.reject');

});
```

---

## 12. Types TypeScript

Localização: `resources/js/types/`

### `types/team.d.ts`

```ts
import type { SportMode } from './catalog/sport-mode';
import type { Position } from './catalog/position';

export interface Team {
    id: number;
    name: string;
    description: string | null;
    badge: string | null;
    is_active: boolean;
    owner: UserMinimal;
    sport_modes: TeamSportMode[];
    created_at: string;
    updated_at: string;
}

export interface TeamSportMode {
    id: number;
    sport_mode: SportMode;
    member_count: number;
}
```

### `types/player.d.ts`

```ts
export interface Player {
    user_id: number;
    name: string;
    avatar: string | null;
    birth_date: string | null;
    is_discoverable: boolean;
    history_public: boolean;
    city: string | null;
    state: string | null;
    country: string | null;
    created_at: string;
    updated_at: string;
}

export interface PlayerMembership {
    id: number;
    player: Player;
    position: Position | null;
    is_starter: boolean;
    left_at: string | null;
    joined_at: string;
}
```

### `types/invitation.d.ts`

```ts
export type InvitationStatus = 'pending' | 'accepted' | 'rejected' | 'expired';

export interface TeamInvitation {
    id: number;
    team_sport_mode: TeamSportMode;
    invited_user: UserMinimal;
    position: Position | null;
    status: InvitationStatus;
    expires_at: string | null;
    message: string | null;
    created_at: string;
}
```

### `types/user.d.ts`

```ts
export interface UserMinimal {
    id: number;
    name: string;
    avatar: string | null;
}
```

---

## 13. Testes

Localização: `tests/Feature/Api/`

### 13.1 `PlayerTest`

```php
class PlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_player_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/players', [
            'city'           => 'São Paulo',
            'state'          => 'SP',
            'country'        => 'BR',
            'is_discoverable' => true,
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.city', 'São Paulo');
        $this->assertDatabaseHas('players', ['user_id' => $user->id, 'city' => 'São Paulo']);
    }

    public function test_cannot_create_duplicate_player_profile(): void
    {
        $user = User::factory()->create();
        Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->postJson('/api/v1/players', [])
             ->assertStatus(409);
    }

    public function test_user_can_update_own_player_profile(): void
    {
        $user   = User::factory()->create();
        Player::factory()->create(['user_id' => $user->id, 'city' => 'Belo Horizonte']);

        $response = $this->actingAs($user)->putJson('/api/v1/players', ['city' => 'Curitiba']);

        $response->assertOk()->assertJsonPath('data.city', 'Curitiba');
    }

    public function test_player_profile_requires_authentication(): void
    {
        $this->postJson('/api/v1/players', [])->assertUnauthorized();
        $this->putJson('/api/v1/players', [])->assertUnauthorized();
    }

    public function test_sensitive_fields_hidden_from_other_users(): void
    {
        $owner  = User::factory()->create();
        $viewer = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $owner->id, 'cpf' => '12345678901']);

        $this->actingAs($viewer)->getJson("/api/v1/players/{$owner->id}")
             ->assertOk()
             ->assertJsonMissingPath('data.cpf');
    }
}
```

### 13.2 `TeamTest`

```php
class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_team(): void
    {
        $user      = User::factory()->create();
        $sportMode = SportMode::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/teams', [
            'name'           => 'Os Crias FC',
            'sport_mode_ids' => [$sportMode->id],
        ]);

        $response->assertCreated()
                 ->assertJsonPath('data.name', 'Os Crias FC');
        $this->assertDatabaseHas('teams', ['name' => 'Os Crias FC', 'owner_id' => $user->id]);
        $this->assertDatabaseHas('team_sport_modes', ['sport_mode_id' => $sportMode->id]);
    }

    public function test_only_owner_can_update_team(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $team  = Team::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($other)->putJson("/api/v1/teams/{$team->id}", ['name' => 'Invasores'])
             ->assertForbidden();
    }

    public function test_owner_can_deactivate_team(): void
    {
        $owner = User::factory()->create();
        $team  = Team::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)->deleteJson("/api/v1/teams/{$team->id}")
             ->assertOk();
        $this->assertDatabaseHas('teams', ['id' => $team->id, 'is_active' => false]);
    }

    public function test_owner_can_list_own_teams(): void
    {
        $owner = User::factory()->create();
        Team::factory()->count(3)->create(['owner_id' => $owner->id]);
        Team::factory()->create(); // time de outro usuário

        $this->actingAs($owner)->getJson('/api/v1/teams')
             ->assertOk()
             ->assertJsonCount(3, 'data');
    }

    public function test_team_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/teams')->assertUnauthorized();
        $this->postJson('/api/v1/teams')->assertUnauthorized();
    }
}
```

### 13.3 `TeamInvitationTest`

```php
class TeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_send_invitation(): void
    {
        $owner     = User::factory()->create();
        $invited   = User::factory()->create();
        $sportMode = SportMode::factory()->create();
        $team      = Team::factory()->create(['owner_id' => $owner->id]);
        $tsm       = TeamSportMode::factory()->create(['team_id' => $team->id, 'sport_mode_id' => $sportMode->id]);

        $response = $this->actingAs($owner)->postJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/invitations",
            ['invited_user_id' => $invited->id]
        );

        $response->assertCreated();
        $this->assertDatabaseHas('team_invitations', [
            'invited_user_id' => $invited->id,
            'status'          => 'pending',
        ]);
    }

    public function test_non_owner_cannot_send_invitation(): void
    {
        $other   = User::factory()->create();
        $invited = User::factory()->create();
        $team    = Team::factory()->create();
        $tsm     = TeamSportMode::factory()->create(['team_id' => $team->id]);

        $this->actingAs($other)->postJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/invitations",
            ['invited_user_id' => $invited->id]
        )->assertForbidden();
    }

    public function test_invited_user_can_accept_invitation(): void
    {
        $invited    = User::factory()->create();
        $invitation = TeamInvitation::factory()->create([
            'invited_user_id' => $invited->id,
            'status'          => 'pending',
        ]);

        $this->actingAs($invited)->postJson("/api/v1/invitations/{$invitation->id}/accept")
             ->assertOk();

        $this->assertDatabaseHas('team_invitations', ['id' => $invitation->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('player_memberships', ['player_id' => $invited->id]);
    }

    public function test_invited_user_can_reject_invitation(): void
    {
        $invited    = User::factory()->create();
        $invitation = TeamInvitation::factory()->create([
            'invited_user_id' => $invited->id,
            'status'          => 'pending',
        ]);

        $this->actingAs($invited)->postJson("/api/v1/invitations/{$invitation->id}/reject")
             ->assertOk();

        $this->assertDatabaseHas('team_invitations', ['id' => $invitation->id, 'status' => 'rejected']);
    }

    public function test_user_cannot_accept_invitation_not_addressed_to_them(): void
    {
        $other      = User::factory()->create();
        $invitation = TeamInvitation::factory()->create(['status' => 'pending']);

        $this->actingAs($other)->postJson("/api/v1/invitations/{$invitation->id}/accept")
             ->assertForbidden();
    }

    public function test_cannot_accept_already_accepted_invitation(): void
    {
        $invited    = User::factory()->create();
        $invitation = TeamInvitation::factory()->create([
            'invited_user_id' => $invited->id,
            'status'          => 'accepted',
        ]);

        $this->actingAs($invited)->postJson("/api/v1/invitations/{$invitation->id}/accept")
             ->assertStatus(409);
    }

    public function test_invitation_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/invitations')->assertUnauthorized();
    }
}
```

### 13.4 `TeamRosterTest`

```php
class TeamRosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_remove_player_from_roster(): void
    {
        $owner      = User::factory()->create();
        $team       = Team::factory()->create(['owner_id' => $owner->id]);
        $tsm        = TeamSportMode::factory()->create(['team_id' => $team->id]);
        $player     = User::factory()->create();
        $membership = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $tsm->id,
            'player_id'          => $player->id,
        ]);

        $this->actingAs($owner)->deleteJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/members/{$membership->id}"
        )->assertOk();

        $this->assertDatabaseHas('player_memberships', [
            'id'      => $membership->id,
            'left_at' => now(),
        ]);
    }

    public function test_player_can_leave_team_themselves(): void
    {
        $player     = User::factory()->create();
        $team       = Team::factory()->create();
        $tsm        = TeamSportMode::factory()->create(['team_id' => $team->id]);
        $membership = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $tsm->id,
            'player_id'          => $player->id,
            'left_at'            => null,
        ]);

        $this->actingAs($player)->deleteJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/members/{$membership->id}/leave"
        )->assertOk();

        $this->assertDatabaseHas('player_memberships', [
            'id'      => $membership->id,
            'left_at' => now()->toDateTimeString(),
        ]);
    }

    public function test_player_cannot_remove_other_player_via_leave_endpoint(): void
    {
        $player     = User::factory()->create();
        $other      = User::factory()->create();
        $team       = Team::factory()->create();
        $tsm        = TeamSportMode::factory()->create(['team_id' => $team->id]);
        $membership = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $tsm->id,
            'player_id'          => $other->id,
        ]);

        $this->actingAs($player)->deleteJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/members/{$membership->id}/leave"
        )->assertForbidden();
    }

    public function test_non_owner_cannot_remove_player(): void
    {
        $other      = User::factory()->create();
        $team       = Team::factory()->create();
        $tsm        = TeamSportMode::factory()->create(['team_id' => $team->id]);
        $membership = PlayerMembership::factory()->create(['team_sport_mode_id' => $tsm->id]);

        $this->actingAs($other)->deleteJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/members/{$membership->id}"
        )->assertForbidden();
    }

    public function test_can_list_active_roster(): void
    {
        $owner  = User::factory()->create();
        $team   = Team::factory()->create(['owner_id' => $owner->id]);
        $tsm    = TeamSportMode::factory()->create(['team_id' => $team->id]);

        PlayerMembership::factory()->count(5)->create(['team_sport_mode_id' => $tsm->id, 'left_at' => null]);
        PlayerMembership::factory()->create(['team_sport_mode_id' => $tsm->id, 'left_at' => now()]); // saiu

        $this->actingAs($owner)->getJson(
            "/api/v1/teams/{$team->id}/sport-modes/{$tsm->id}/members"
        )->assertOk()->assertJsonCount(5, 'data');
    }
}
```

---

## 14. Factories necessárias

```
database/factories/
├── PlayerFactory.php
├── StaffMemberFactory.php
├── TeamFactory.php
├── TeamSportModeFactory.php
├── TeamStaffFactory.php
├── PlayerMembershipFactory.php
└── TeamInvitationFactory.php
```

`TeamFactory`:

```php
public function definition(): array
{
    return [
        'owner_id'    => User::factory(),
        'name'        => fake()->company() . ' FC',
        'description' => fake()->sentence(),
        'is_active'   => true,
    ];
}
```

`TeamInvitationFactory`:

```php
public function definition(): array
{
    return [
        'team_sport_mode_id' => TeamSportMode::factory(),
        'invited_user_id'    => User::factory(),
        'invited_by'         => User::factory(),
        'status'             => 'pending',
        'expires_at'         => now()->addDays(7),
    ];
}
```

---

## 15. Checklist de Conclusão

### Banco

- [ ] Migration `players`
- [ ] Migration `staff_members`
- [ ] Migration `teams`
- [ ] Migration `team_sport_modes`
- [ ] Migration `team_staff`
- [ ] Migration `player_memberships`
- [ ] Migration `team_invitations`

### Enums e Models

- [ ] Enum `InvitationStatus`
- [ ] Model `Player`
- [ ] Model `StaffMember`
- [ ] Model `Team`
- [ ] Model `TeamSportMode`
- [ ] Model `TeamStaff`
- [ ] Model `PlayerMembership`
- [ ] Model `TeamInvitation`

### Backend

- [ ] `PlayerService`
- [ ] `StaffMemberService`
- [ ] `TeamService`
- [ ] `TeamRosterService` (métodos: `removeMember`, `leaveTeam`, `acceptInvitation`)
- [ ] `TeamInvitationService`
- [ ] Job `ExpireTeamInvitations` (+ schedule horário)
- [ ] Form Requests (StorePlayer, UpdatePlayer, StoreTeam, UpdateTeam, StoreTeamInvitation, StoreStaffMember)
- [ ] Resources (Player, Team, TeamSportMode, PlayerMembership, TeamInvitation, UserMinimal)
- [ ] `TeamPolicy` registrada com bypass `isAdmin()`
- [ ] Controllers (Player, StaffMember, Team, TeamSportMode, TeamRoster com `leave`, TeamInvitation)
- [ ] Rotas registradas em `routes/api.php`

### Frontend (Types)

- [ ] `types/team.d.ts`
- [ ] `types/player.d.ts`
- [ ] `types/invitation.d.ts`
- [ ] `types/user.d.ts`

### Testes

- [ ] Factories (7)
- [ ] `PlayerTest`
- [ ] `TeamTest`
- [ ] `TeamInvitationTest`
- [ ] `TeamRosterTest` (incluindo `test_player_can_leave_team_themselves`)
- [ ] Todos os testes passando (`php artisan test`)

---

## 16. Comandos de Referência

```bash
# Migrations
php artisan make:migration create_players_table
php artisan make:migration create_staff_members_table
php artisan make:migration create_teams_table
php artisan make:migration create_team_sport_modes_table
php artisan make:migration create_team_staff_table
php artisan make:migration create_player_memberships_table
php artisan make:migration create_team_invitations_table

php artisan migrate

# Models
php artisan make:model Player
php artisan make:model StaffMember
php artisan make:model Team
php artisan make:model TeamSportMode
php artisan make:model TeamStaff
php artisan make:model PlayerMembership
php artisan make:model TeamInvitation

# Enum
php artisan make:enum Enums/InvitationStatus

# Factories
php artisan make:factory PlayerFactory --model=Player
php artisan make:factory TeamFactory --model=Team
php artisan make:factory TeamSportModeFactory --model=TeamSportMode
php artisan make:factory TeamInvitationFactory --model=TeamInvitation
php artisan make:factory PlayerMembershipFactory --model=PlayerMembership

# Services (criar manualmente — sem artisan)
# app/Services/Player/PlayerService.php
# app/Services/Staff/StaffMemberService.php
# app/Services/Team/TeamService.php
# app/Services/Team/TeamRosterService.php
# app/Services/Team/TeamInvitationService.php

# Job
php artisan make:job ExpireTeamInvitations

# Requests
php artisan make:request StorePlayerRequest
php artisan make:request UpdatePlayerRequest
php artisan make:request StoreTeamRequest
php artisan make:request UpdateTeamRequest
php artisan make:request StoreTeamInvitationRequest
php artisan make:request StoreStaffMemberRequest

# Resources
php artisan make:resource PlayerResource
php artisan make:resource TeamResource
php artisan make:resource TeamSportModeResource
php artisan make:resource PlayerMembershipResource
php artisan make:resource TeamInvitationResource
php artisan make:resource UserMinimalResource

# Policy
php artisan make:policy TeamPolicy --model=Team

# Testes
php artisan test
php artisan test --filter=PlayerTest
php artisan test --filter=TeamTest
php artisan test --filter=TeamInvitationTest
php artisan test --filter=TeamRosterTest
```
