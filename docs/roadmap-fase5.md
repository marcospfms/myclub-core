# Roadmap Fase 5 — Player Pro e Descoberta de Jogadores

> Detalhamento completo de implementação da Fase 5. Cobertura: migrations, models, services, form requests, resources, policy, controllers, rotas, types TypeScript, factories e testes.
>
> **Pré-requisito:** Fase 4 concluída (`users.plan` e `UserPlan` enum disponíveis; `PlanGatingService` e middleware `RequirePlan` ativos).
>
> Referências de schema: `docs/database/schema.md` §1 (Identidade e Usuários).
> Referências de produto: `docs/product/feature-gating.md`, `docs/product/user-personas.md`, `docs/product/player-membership-rules.md`, `docs/product/authorization-rules.md`.
> Referências de padrões: `docs/patterns/`.

---

## 1. Objetivo

Implementar o perfil público do jogador, o sistema de descoberta (busca por posição, modalidade e localização) e os benefícios de visibilidade do plano **Player Pro**: URL amigável `@slug`, cartão digital e destaque em resultados de busca. Resolve a decisão aberta em `docs/product/user-personas.md §3.1` sobre quem pode buscar e ser descoberto.

---

## 2. Escopo

| Entrega                                           | Descrição                                                                                                        |
| ------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Migration `add_slug_to_users_table`               | Coluna `slug` em `users` — URL amigável, exclusiva de Player Pro+                                                |
| Migration `add_discovery_fields_to_players_table` | Colunas `is_discoverable`, `history_public`, `city`, `state`, `country` em `players`                             |
| Migration `create_player_sport_preferences_table` | Tabela de preferências de modalidade/posição para descoberta                                                     |
| Atualização model `User`                          | `slug` em `$fillable`, cast, helper `hasPublicProfile()`                                                         |
| Atualização model `Player`                        | Novos campos em `$fillable`, casts, relacionamentos `user` e `sportPreferences`                                  |
| Model `PlayerSportPreference`                     | Preferência de modalidade/posição por jogador                                                                    |
| `PlayerProfileService`                            | Gestão de slug, configurações de descoberta e sincronização de preferências                                      |
| `PlayerDiscoveryService`                          | Busca de jogadores com filtros, guard de plano e ordenação por Player Pro                                        |
| `PlayerCardService`                               | Dados estruturados do cartão digital; valida plano para exportação                                               |
| Form Requests (4)                                 | `UpdatePlayerDiscoveryRequest`, `UpdatePlayerSlugRequest`, `SyncPlayerPreferencesRequest`, `PlayerSearchRequest` |
| API Resources (3)                                 | `PlayerPublicResource`, `PlayerPreferenceResource`, `PlayerCardResource`                                         |
| `PlayerPolicy`                                    | Autorização: visualizar perfil, editar descoberta, exportar cartão, buscar                                       |
| Controllers (3)                                   | `PlayerProfileController`, `PlayerDiscoveryController`, `PlayerPreferenceController`                             |
| Rotas API                                         | Perfil público, busca, preferências, cartão digital                                                              |
| Types TypeScript                                  | `PlayerPublic`, `PlayerPreference`, `PlayerCard`, `PlayerDiscoveryFilters`                                       |
| Factory `PlayerSportPreferenceFactory`            | Estado `availableForInvite()` + state `discoverable()` em `PlayerFactory`                                        |
| Testes Feature (4 classes)                        | Descoberta, perfil, slug, cartão                                                                                 |

### Progresso atual

⬜ Nenhum bloco desta fase foi iniciado.

---

## 3. Decisões de modelagem

### 3.1 Quem pode buscar jogadores

A busca de jogadores é restrita a usuários com plano **Club ou superior** (`isPlanAtLeast(UserPlan::Club)`). Administradores têm acesso irrestrito. Usuários Free e Player Pro não têm acesso à ferramenta de descoberta.

**Motivação:** a busca é uma ferramenta de recrutamento de elenco, não de consumo individual. O público-alvo são gestores de time ativos — personas que se enquadram naturalmente no plano Club.

### 3.2 Quem aparece nas buscas

Um jogador aparece em resultados se, e somente se:

- `players.is_discoverable = true` — o jogador optou por ser visível
- tem ao menos uma `player_sport_preferences` com `available_for_invite = true` na modalidade pesquisada

O Player Pro **não é pré-requisito** para ser descobrível — qualquer jogador pode ativar `is_discoverable`. O Player Pro adiciona **destaque** no resultado (campo `is_pro: true` no resource, ordenação prioritária) e a badge identificadora no perfil.

### 3.3 URL amigável (`@slug`)

O `slug` em `users` é exclusivo de Player Pro+. Usuários Free e Club têm perfil público via `/players/{id}` (URL opaca). Player Pro+ podem definir um slug e ser acessados via `/players/@username`.

Regras:

- 3–30 caracteres, apenas letras minúsculas, números e hífen (`^[a-z0-9-]+$`)
- Único na tabela `users`
- O próprio usuário gerencia via endpoint `PATCH /api/v1/me/player/slug`
- Não há restrição de frequência de alteração nesta fase

### 3.4 Cartão digital exportável

O cartão digital é renderizado no frontend (Vue component). O backend serve os **dados estruturados** via `PlayerCardResource`. O botão de export em alta resolução é habilitado para Player Pro+ via campo `export_enabled: true` na resposta — o processamento de imagem é inteiramente frontend.

### 3.5 Histórico público

`players.history_public` controla se outros times podem ver o histórico do jogador (times anteriores, estatísticas). Padrão `false`. Controlado pelo próprio jogador — não depende de plano.

### 3.6 Rota de slug vs rota de busca

`GET /api/v1/players/{identifier}` e `GET /api/v1/players/search` compartilham o mesmo prefixo. Para evitar que `search` seja interpretado como `identifier`, a rota de busca usa o prefixo `discovery`:

```
GET /api/v1/players/{identifier}           → perfil público (id ou @slug)
GET /api/v1/players/discovery/search       → busca com filtros (Club+)
```

---

## 4. Contexto de Domínio

```
users (Fase 0)
 ├── slug                              ← novo nesta fase (Player Pro+)
 └── players (Fase 1)
      ├── is_discoverable              ← novo nesta fase
      ├── history_public               ← novo nesta fase
      ├── city / state / country       ← novo nesta fase
      ├── player_sport_preferences     ← novo nesta fase
      │    ├── sport_mode_id  → sport_modes
      │    ├── position_id    → positions
      │    └── available_for_invite
      └── player_memberships (Fase 1)
           └── team_sport_modes → teams (exposto se history_public = true)
```

Fluxo de descoberta:

```
[Club+ busca: sport_mode_id + position_id? + city? + state?]
  → PlayerDiscoveryService::search()
  → filtra: is_discoverable = true
            AND player_sport_preferences.available_for_invite = true
            AND sport_mode_id = filtro
  → Player Pro aparecem com is_pro: true (ordenação prioritária)
  → retorna PlayerPublicResource[] paginado
```

---

## 5. Migrations

### 5.1 `add_slug_to_users_table`

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('slug', 30)->nullable()->unique()->after('plan');
});
```

### 5.2 `add_discovery_fields_to_players_table`

```php
Schema::table('players', function (Blueprint $table) {
    $table->boolean('is_discoverable')->default(false)->after('phone');
    $table->boolean('history_public')->default(false)->after('is_discoverable');
    $table->string('city', 100)->nullable()->after('history_public');
    $table->string('state', 60)->nullable()->after('city');
    $table->char('country', 2)->default('BR')->after('state');
});
```

### 5.3 `create_player_sport_preferences_table`

```php
Schema::create('player_sport_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('player_id')->constrained('players', 'user_id')->cascadeOnDelete();
    $table->foreignId('sport_mode_id')->constrained()->restrictOnDelete();
    $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
    $table->boolean('available_for_invite')->default(false);
    $table->timestamps();

    $table->unique(['player_id', 'sport_mode_id']);
});
```

> `player_id` referencia `players.user_id` — a PK da tabela `players` é `user_id`. A unicidade composta `(player_id, sport_mode_id)` garante uma preferência por modalidade por jogador.

---

## 6. Models

### 6.1 `User` — atualizações

Adicionar à classe `User` existente:

```php
// Em $fillable — acrescentar:
'slug',

// Em casts():
'slug' => 'string',

// Helper:

/**
 * Retorna true se o usuário tem Player Pro+ E slug definido.
 * Indica que o perfil é acessível via URL amigável.
 */
public function hasPublicProfile(): bool
{
    return $this->isPlanAtLeast(UserPlan::PlayerPro) && !empty($this->slug);
}

// Relacionamento (caso não exista):
public function player(): HasOne
{
    return $this->hasOne(Player::class, 'user_id');
}
```

### 6.2 `Player` — atualizações

Adicionar à classe `Player` existente (ou criar em `app/Models/Player.php`):

```php
class Player extends Model
{
    protected $table = 'players';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'cpf', 'rg', 'birth_date', 'phone',
        'is_discoverable', 'history_public',
        'city', 'state', 'country',
    ];

    protected function casts(): array
    {
        return [
            'birth_date'      => 'date',
            'is_discoverable' => 'boolean',
            'history_public'  => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sportPreferences(): HasMany
    {
        return $this->hasMany(PlayerSportPreference::class, 'player_id', 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(PlayerMembership::class, 'player_id', 'user_id');
    }
}
```

### 6.3 `PlayerSportPreference` (novo)

Localização: `app/Models/PlayerSportPreference.php`

```php
class PlayerSportPreference extends Model
{
    protected $table = 'player_sport_preferences';

    protected $fillable = [
        'player_id', 'sport_mode_id', 'position_id', 'available_for_invite',
    ];

    protected function casts(): array
    {
        return [
            'available_for_invite' => 'boolean',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id', 'user_id');
    }

    public function sportMode(): BelongsTo
    {
        return $this->belongsTo(SportMode::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
```

---

## 7. Services

Localização: `app/Services/Players/`

| Service                  | Arquivo                                           |
| ------------------------ | ------------------------------------------------- |
| `PlayerProfileService`   | `app/Services/Players/PlayerProfileService.php`   |
| `PlayerDiscoveryService` | `app/Services/Players/PlayerDiscoveryService.php` |
| `PlayerCardService`      | `app/Services/Players/PlayerCardService.php`      |

### 7.1 `PlayerProfileService`

```php
class PlayerProfileService
{
    /**
     * Atualiza configurações de descoberta e localização do jogador.
     */
    public function updateDiscovery(Player $player, array $data): Player
    {
        $player->update(array_intersect_key($data, array_flip([
            'is_discoverable', 'history_public', 'city', 'state', 'country',
        ])));

        return $player->refresh();
    }

    /**
     * Define ou atualiza o slug do usuário. Requer Player Pro ou superior.
     *
     * @throws \DomainException se o plano for insuficiente ou o slug já estiver em uso
     */
    public function updateSlug(User $user, string $slug): User
    {
        if (!$user->isPlanAtLeast(UserPlan::PlayerPro)) {
            throw new \DomainException('URL amigável é exclusiva do plano Player Pro ou superior.');
        }

        $slug = Str::lower(trim($slug));

        $alreadyTaken = User::where('slug', $slug)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($alreadyTaken) {
            throw new \DomainException('Este slug já está em uso.');
        }

        $user->update(['slug' => $slug]);

        return $user->refresh();
    }

    /**
     * Sincroniza as preferências de modalidade/posição do jogador.
     * Cria, atualiza ou remove conforme o array recebido.
     *
     * @param  array<int, array{sport_mode_id: int, position_id: ?int, available_for_invite: bool}>  $preferences
     */
    public function syncPreferences(Player $player, array $preferences): Player
    {
        $syncData = collect($preferences)
            ->keyBy('sport_mode_id')
            ->map(fn ($pref) => [
                'position_id'          => $pref['position_id'] ?? null,
                'available_for_invite' => $pref['available_for_invite'] ?? false,
            ])
            ->all();

        DB::transaction(function () use ($player, $syncData) {
            // Remove as preferências que não estão no novo array
            $player->sportPreferences()
                ->whereNotIn('sport_mode_id', array_keys($syncData))
                ->delete();

            // Upsert das presentes
            foreach ($syncData as $sportModeId => $attrs) {
                $player->sportPreferences()->updateOrCreate(
                    ['sport_mode_id' => $sportModeId],
                    $attrs
                );
            }
        });

        return $player->load('sportPreferences.sportMode', 'sportPreferences.position');
    }
}
```

### 7.2 `PlayerDiscoveryService`

```php
class PlayerDiscoveryService
{
    /**
     * Busca jogadores disponíveis para convite.
     * Requer plano Club ou superior para o usuário que busca.
     *
     * Filtros aceitos:
     *   - sport_mode_id (obrigatório)
     *   - position_id   (opcional)
     *   - city          (opcional, like)
     *   - state         (opcional, exact)
     *
     * @throws \DomainException se o usuário buscador não tiver plano Club+
     */
    public function search(User $searcher, array $filters): LengthAwarePaginator
    {
        if (!$searcher->isAdmin() && !$searcher->isPlanAtLeast(UserPlan::Club)) {
            throw new \DomainException('A busca de jogadores é exclusiva do plano Club ou superior.');
        }

        $query = Player::query()
            ->with(['user', 'sportPreferences.sportMode', 'sportPreferences.position'])
            ->where('is_discoverable', true)
            ->whereHas('sportPreferences', function ($q) use ($filters) {
                $q->where('available_for_invite', true)
                  ->where('sport_mode_id', $filters['sport_mode_id']);

                if (!empty($filters['position_id'])) {
                    $q->where('position_id', $filters['position_id']);
                }
            });

        if (!empty($filters['city'])) {
            $query->where('city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        // Player Pro aparece em destaque no topo dos resultados
        $query->orderByRaw(
            "CASE WHEN (SELECT plan FROM users WHERE id = players.user_id) = 'player_pro' THEN 0 ELSE 1 END"
        )->orderBy('user_id');

        return $query->paginate(20);
    }
}
```

### 7.3 `PlayerCardService`

```php
class PlayerCardService
{
    /**
     * Retorna os dados estruturados para renderização do cartão digital.
     * A exportação em alta resolução é habilitada apenas para Player Pro+.
     *
     * @throws \DomainException se $forExport = true e o usuário não tiver Player Pro
     */
    public function getCardData(Player $player, bool $forExport = false): array
    {
        $user = $player->user;

        if ($forExport && !$user->isPlanAtLeast(UserPlan::PlayerPro)) {
            throw new \DomainException('Exportação do cartão é exclusiva do plano Player Pro ou superior.');
        }

        return [
            'name'           => $user->name,
            'avatar'         => $user->avatar,
            'slug'           => $user->slug,
            'plan'           => $user->plan->value,
            'is_pro'         => $user->isPlanAtLeast(UserPlan::PlayerPro),
            'city'           => $player->city,
            'state'          => $player->state,
            'country'        => $player->country,
            'preferences'    => $player->sportPreferences->map(fn ($pref) => [
                'sport_mode' => $pref->sportMode?->name,
                'position'   => $pref->position?->abbreviation,
            ]),
            'badges_count'   => $player->badges()->count(),
            'export_enabled' => $user->isPlanAtLeast(UserPlan::PlayerPro),
        ];
    }
}
```

> `$player->badges()` pressupõe o relacionamento `Player::badges()` implementado na Fase 3 (`player_badges`). Se não existir, usar `0` como fallback nesta fase.

---

## 8. Form Requests

Localização: `app/Http/Requests/`

### 8.1 `UpdatePlayerDiscoveryRequest`

```php
class UpdatePlayerDiscoveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->player !== null;
    }

    public function rules(): array
    {
        return [
            'is_discoverable' => ['sometimes', 'boolean'],
            'history_public'  => ['sometimes', 'boolean'],
            'city'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'state'           => ['sometimes', 'nullable', 'string', 'max:60'],
            'country'         => ['sometimes', 'nullable', 'string', 'size:2'],
        ];
    }
}
```

### 8.2 `UpdatePlayerSlugRequest`

```php
class UpdatePlayerSlugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('users', 'slug')->ignore($this->user()->id),
            ],
        ];
    }
}
```

### 8.3 `SyncPlayerPreferencesRequest`

```php
class SyncPlayerPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->player !== null;
    }

    public function rules(): array
    {
        return [
            'preferences'                        => ['required', 'array'],
            'preferences.*.sport_mode_id'        => ['required', 'integer', 'exists:sport_modes,id'],
            'preferences.*.position_id'          => ['nullable', 'integer', 'exists:positions,id'],
            'preferences.*.available_for_invite' => ['required', 'boolean'],
        ];
    }
}
```

### 8.4 `PlayerSearchRequest`

```php
class PlayerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sport_mode_id' => ['required', 'integer', 'exists:sport_modes,id'],
            'position_id'   => ['nullable', 'integer', 'exists:positions,id'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:60'],
        ];
    }
}
```

---

## 9. API Resources

Localização: `app/Http/Resources/`

### 9.1 `PlayerPublicResource`

```php
class PlayerPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->user_id,
            'name'        => $this->user->name,
            'avatar'      => $this->user->avatar,
            'slug'        => $this->user->slug,
            'is_pro'      => $this->user->isPlanAtLeast(UserPlan::PlayerPro),
            'city'        => $this->city,
            'state'       => $this->state,
            'country'     => $this->country,
            'preferences' => PlayerPreferenceResource::collection(
                $this->whenLoaded('sportPreferences')
            ),
        ];
    }
}
```

### 9.2 `PlayerPreferenceResource`

```php
class PlayerPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'sport_mode_id'        => $this->sport_mode_id,
            'sport_mode'           => $this->whenLoaded('sportMode', fn () => $this->sportMode->name),
            'position_id'          => $this->position_id,
            'position'             => $this->whenLoaded('position', fn () => $this->position?->abbreviation),
            'available_for_invite' => $this->available_for_invite,
        ];
    }
}
```

### 9.3 `PlayerCardResource`

```php
class PlayerCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return $this->resource; // array estruturado retornado por PlayerCardService::getCardData()
    }
}
```

---

## 10. Policy

Localização: `app/Policies/PlayerPolicy.php`

```php
class PlayerPolicy
{
    /**
     * Qualquer visitante (anônimo ou autenticado) pode ver perfis públicos.
     */
    public function viewPublic(?User $user, Player $player): bool
    {
        return true;
    }

    /**
     * Apenas o próprio jogador ou admin pode editar configurações de descoberta.
     */
    public function update(User $user, Player $player): bool
    {
        return $user->id === $player->user_id || $user->isAdmin();
    }

    /**
     * Exportação do cartão: o próprio jogador com Player Pro+ ou admin.
     */
    public function exportCard(User $user, Player $player): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $player->user_id
            && $user->isPlanAtLeast(UserPlan::PlayerPro);
    }

    /**
     * Busca de jogadores: Club+ ou admin.
     */
    public function search(User $user): bool
    {
        return $user->isAdmin() || $user->isPlanAtLeast(UserPlan::Club);
    }
}
```

Registrar em `app/Providers/AppServiceProvider.php`:

```php
Gate::policy(Player::class, PlayerPolicy::class);
```

---

## 11. Controllers

Localização: `app/Http/Controllers/Api/`

### 11.1 `PlayerProfileController`

```
GET    /api/v1/players/{identifier}     → show         (perfil público — id ou @slug)
PATCH  /api/v1/me/player/discovery      → update       (configurações de descoberta)
PATCH  /api/v1/me/player/slug           → updateSlug   (URL amigável — Player Pro+)
GET    /api/v1/me/player/card           → card         (dados do cartão digital)
```

```php
class PlayerProfileController extends BaseController
{
    public function __construct(
        private PlayerProfileService $profileService,
        private PlayerCardService $cardService,
    ) {}

    public function show(string $identifier): JsonResponse
    {
        $player = Str::startsWith($identifier, '@')
            ? Player::whereHas('user', fn ($q) => $q->where('slug', ltrim($identifier, '@')))->firstOrFail()
            : Player::findOrFail($identifier);

        $this->authorize('viewPublic', $player);

        return $this->sendResponse(
            new PlayerPublicResource(
                $player->load('sportPreferences.sportMode', 'sportPreferences.position')
            ),
            'Perfil recuperado.'
        );
    }

    public function update(UpdatePlayerDiscoveryRequest $request): JsonResponse
    {
        $player = $request->user()->player;

        abort_unless($player, 404, 'Perfil de jogador não encontrado.');

        $this->authorize('update', $player);

        $player = $this->profileService->updateDiscovery($player, $request->validated());

        return $this->sendResponse(
            new PlayerPublicResource($player->load('sportPreferences')),
            'Configurações de descoberta atualizadas.'
        );
    }

    public function updateSlug(UpdatePlayerSlugRequest $request): JsonResponse
    {
        $user = $this->profileService->updateSlug(
            $request->user(),
            $request->validated('slug')
        );

        return $this->sendResponse(['slug' => $user->slug], 'Slug atualizado.');
    }

    public function card(Request $request): JsonResponse
    {
        $player = $request->user()->player;

        abort_unless($player, 404, 'Perfil de jogador não encontrado.');

        $forExport = (bool) $request->query('export', false);

        if ($forExport) {
            $this->authorize('exportCard', $player);
        }

        $data = $this->cardService->getCardData($player, $forExport);

        return $this->sendResponse(new PlayerCardResource($data), 'Cartão recuperado.');
    }
}
```

### 11.2 `PlayerDiscoveryController`

```
GET  /api/v1/players/discovery/search   → index   (busca com filtros — Club+)
```

```php
class PlayerDiscoveryController extends BaseController
{
    public function __construct(
        private PlayerDiscoveryService $discoveryService,
    ) {}

    public function index(PlayerSearchRequest $request): JsonResponse
    {
        $this->authorize('search', Player::class);

        $results = $this->discoveryService->search($request->user(), $request->validated());

        return $this->sendResponse(
            PlayerPublicResource::collection($results),
            'Jogadores encontrados.'
        );
    }
}
```

### 11.3 `PlayerPreferenceController`

```
PUT  /api/v1/me/player/preferences   → sync   (sincroniza preferências completas)
```

```php
class PlayerPreferenceController extends BaseController
{
    public function __construct(
        private PlayerProfileService $profileService,
    ) {}

    public function sync(SyncPlayerPreferencesRequest $request): JsonResponse
    {
        $player = $request->user()->player;

        abort_unless($player, 404, 'Perfil de jogador não encontrado.');

        $this->authorize('update', $player);

        $player = $this->profileService->syncPreferences(
            $player,
            $request->validated('preferences')
        );

        return $this->sendResponse(
            PlayerPreferenceResource::collection($player->sportPreferences),
            'Preferências atualizadas.'
        );
    }
}
```

---

## 12. Rotas

```php
// Público — sem autenticação
Route::get('v1/players/{identifier}', [Api\PlayerProfileController::class, 'show'])
    ->name('api.players.show');

Route::middleware('auth:sanctum')->group(function () {

    // Busca de jogadores — Club+
    // Prefixo 'discovery' evita conflito com {identifier} da rota acima
    Route::get('v1/players/discovery/search', [Api\PlayerDiscoveryController::class, 'index'])
        ->name('api.players.discovery.search');

    // Perfil do jogador autenticado
    Route::patch('v1/me/player/discovery', [Api\PlayerProfileController::class, 'update'])
        ->name('api.me.player.discovery');

    Route::patch('v1/me/player/slug', [Api\PlayerProfileController::class, 'updateSlug'])
        ->name('api.me.player.slug');

    Route::get('v1/me/player/card', [Api\PlayerProfileController::class, 'card'])
        ->name('api.me.player.card');

    // Preferências de modalidade
    Route::put('v1/me/player/preferences', [Api\PlayerPreferenceController::class, 'sync'])
        ->name('api.me.player.preferences.sync');

});
```

> `GET v1/players/{identifier}` deve ser registrada **antes** das rotas autenticadas para garantir que o parâmetro captura corretamente. A rota de busca usa prefixo `discovery/` precavendo colisão.

---

## 13. Types TypeScript

Localização: `resources/js/types/player.d.ts`

```ts
export interface PlayerPreference {
    sport_mode_id: number;
    sport_mode: string | null;
    position_id: number | null;
    position: string | null;
    available_for_invite: boolean;
}

export interface PlayerPublic {
    id: number;
    name: string;
    avatar: string | null;
    slug: string | null;
    is_pro: boolean;
    city: string | null;
    state: string | null;
    country: string | null;
    preferences: PlayerPreference[];
}

export interface PlayerCard {
    name: string;
    avatar: string | null;
    slug: string | null;
    plan: string;
    is_pro: boolean;
    city: string | null;
    state: string | null;
    country: string | null;
    preferences: { sport_mode: string | null; position: string | null }[];
    badges_count: number;
    export_enabled: boolean;
}

export interface PlayerDiscoveryFilters {
    sport_mode_id: number;
    position_id?: number;
    city?: string;
    state?: string;
}
```

---

## 14. Factories

Localização: `database/factories/`

### `PlayerSportPreferenceFactory`

```php
class PlayerSportPreferenceFactory extends Factory
{
    protected $model = PlayerSportPreference::class;

    public function definition(): array
    {
        return [
            'player_id'            => Player::factory(),
            'sport_mode_id'        => SportMode::factory(),
            'position_id'          => null,
            'available_for_invite' => false,
        ];
    }

    public function availableForInvite(): static
    {
        return $this->state(['available_for_invite' => true]);
    }
}
```

### `PlayerFactory` — novo state `discoverable()`

Adicionar ao `PlayerFactory` existente:

```php
public function discoverable(): static
{
    return $this->state([
        'is_discoverable' => true,
        'city'            => 'São Paulo',
        'state'           => 'SP',
        'country'         => 'BR',
    ]);
}
```

---

## 15. Testes

Localização: `tests/Feature/Api/`

### 15.1 `PlayerDiscoveryTest`

```php
class PlayerDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_user_can_search_players(): void
    {
        $searcher   = User::factory()->create(['plan' => 'club']);
        $sportMode  = SportMode::factory()->create();
        $playerUser = User::factory()->create();
        $player     = Player::factory()->discoverable()->create(['user_id' => $playerUser->id]);

        PlayerSportPreference::factory()->availableForInvite()->create([
            'player_id'     => $player->user_id,
            'sport_mode_id' => $sportMode->id,
        ]);

        $this->actingAs($searcher)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $player->user_id]);
    }

    public function test_free_user_cannot_search_players(): void
    {
        $user      = User::factory()->create(['plan' => 'free']);
        $sportMode = SportMode::factory()->create();

        $this->actingAs($user)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertForbidden();
    }

    public function test_player_pro_user_cannot_search_players(): void
    {
        $user      = User::factory()->create(['plan' => 'player_pro']);
        $sportMode = SportMode::factory()->create();

        $this->actingAs($user)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertForbidden();
    }

    public function test_non_discoverable_player_not_in_results(): void
    {
        $searcher   = User::factory()->create(['plan' => 'club']);
        $sportMode  = SportMode::factory()->create();
        $playerUser = User::factory()->create();
        $player     = Player::factory()->create([
            'user_id'         => $playerUser->id,
            'is_discoverable' => false,
        ]);

        PlayerSportPreference::factory()->availableForInvite()->create([
            'player_id'     => $player->user_id,
            'sport_mode_id' => $sportMode->id,
        ]);

        $this->actingAs($searcher)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertOk()
            ->assertJsonMissing(['id' => $player->user_id]);
    }

    public function test_player_not_available_for_invite_not_in_results(): void
    {
        $searcher   = User::factory()->create(['plan' => 'club']);
        $sportMode  = SportMode::factory()->create();
        $playerUser = User::factory()->create();
        $player     = Player::factory()->discoverable()->create(['user_id' => $playerUser->id]);

        // available_for_invite = false (padrão da factory)
        PlayerSportPreference::factory()->create([
            'player_id'     => $player->user_id,
            'sport_mode_id' => $sportMode->id,
        ]);

        $this->actingAs($searcher)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertOk()
            ->assertJsonMissing(['id' => $player->user_id]);
    }

    public function test_player_pro_appears_first_in_results(): void
    {
        $searcher  = User::factory()->create(['plan' => 'club']);
        $sportMode = SportMode::factory()->create();

        $freeUser = User::factory()->create(['plan' => 'free']);
        $proUser  = User::factory()->create(['plan' => 'player_pro']);

        foreach ([$freeUser, $proUser] as $u) {
            $p = Player::factory()->discoverable()->create(['user_id' => $u->id]);
            PlayerSportPreference::factory()->availableForInvite()->create([
                'player_id'     => $p->user_id,
                'sport_mode_id' => $sportMode->id,
            ]);
        }

        $results = $this->actingAs($searcher)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertOk()
            ->json('data');

        $this->assertTrue($results[0]['is_pro']);
    }

    public function test_admin_can_search_players(): void
    {
        $admin     = User::factory()->create(['role' => 'admin', 'plan' => 'free']);
        $sportMode = SportMode::factory()->create();

        $this->actingAs($admin)
            ->getJson("/api/v1/players/discovery/search?sport_mode_id={$sportMode->id}")
            ->assertOk();
    }
}
```

### 15.2 `PlayerProfileTest`

```php
class PlayerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_profile_accessible_without_auth(): void
    {
        $playerUser = User::factory()->create();
        Player::factory()->create(['user_id' => $playerUser->id]);

        $this->getJson("/api/v1/players/{$playerUser->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $playerUser->id]);
    }

    public function test_player_can_update_discovery_settings(): void
    {
        $user   = User::factory()->create();
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/discovery', [
                'is_discoverable' => true,
                'city'            => 'São Paulo',
                'state'           => 'SP',
            ])
            ->assertOk();

        $this->assertDatabaseHas('players', [
            'user_id'         => $user->id,
            'is_discoverable' => true,
            'city'            => 'São Paulo',
        ]);
    }

    public function test_user_without_player_profile_cannot_update_discovery(): void
    {
        $user = User::factory()->create(); // sem Player

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/discovery', ['is_discoverable' => true])
            ->assertStatus(404);
    }

    public function test_preferences_sync_creates_and_removes_correctly(): void
    {
        $user      = User::factory()->create();
        $player    = Player::factory()->create(['user_id' => $user->id]);
        $sportMode = SportMode::factory()->create();

        $this->actingAs($user)
            ->putJson('/api/v1/me/player/preferences', [
                'preferences' => [
                    ['sport_mode_id' => $sportMode->id, 'position_id' => null, 'available_for_invite' => true],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('player_sport_preferences', [
            'player_id'            => $user->id,
            'sport_mode_id'        => $sportMode->id,
            'available_for_invite' => true,
        ]);

        // Enviar array vazio deve remover a preferência anterior
        $this->actingAs($user)
            ->putJson('/api/v1/me/player/preferences', ['preferences' => []])
            ->assertOk();

        $this->assertDatabaseMissing('player_sport_preferences', [
            'player_id' => $user->id,
        ]);
    }
}
```

### 15.3 `PlayerSlugTest`

```php
class PlayerSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_pro_can_set_slug(): void
    {
        $user = User::factory()->create(['plan' => 'player_pro']);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/slug', ['slug' => 'joao-silva'])
            ->assertOk()
            ->assertJsonFragment(['slug' => 'joao-silva']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'slug' => 'joao-silva']);
    }

    public function test_free_user_cannot_set_slug(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/slug', ['slug' => 'joao-silva'])
            ->assertStatus(422);
    }

    public function test_club_user_cannot_set_slug(): void
    {
        $user = User::factory()->create(['plan' => 'club']);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/slug', ['slug' => 'joao-silva'])
            ->assertStatus(422);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        User::factory()->create(['plan' => 'player_pro', 'slug' => 'joao-silva']);
        $user = User::factory()->create(['plan' => 'player_pro']);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/slug', ['slug' => 'joao-silva'])
            ->assertUnprocessable();
    }

    public function test_slug_with_uppercase_or_spaces_is_rejected(): void
    {
        $user = User::factory()->create(['plan' => 'player_pro']);

        $this->actingAs($user)
            ->patchJson('/api/v1/me/player/slug', ['slug' => 'João Silva'])
            ->assertUnprocessable();
    }

    public function test_profile_accessible_via_slug(): void
    {
        $user = User::factory()->create(['plan' => 'player_pro', 'slug' => 'joao-silva']);
        Player::factory()->create(['user_id' => $user->id]);

        $this->getJson('/api/v1/players/@joao-silva')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'joao-silva']);
    }
}
```

### 15.4 `PlayerCardTest`

```php
class PlayerCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_pro_receives_card_with_export_enabled(): void
    {
        $user   = User::factory()->create(['plan' => 'player_pro']);
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/v1/me/player/card')
            ->assertOk()
            ->assertJsonFragment(['is_pro' => true, 'export_enabled' => true]);
    }

    public function test_free_player_receives_card_with_export_disabled(): void
    {
        $user   = User::factory()->create(['plan' => 'free']);
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/v1/me/player/card')
            ->assertOk()
            ->assertJsonFragment(['export_enabled' => false]);
    }

    public function test_free_player_export_param_is_rejected(): void
    {
        $user   = User::factory()->create(['plan' => 'free']);
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/v1/me/player/card?export=1')
            ->assertForbidden();
    }

    public function test_card_includes_correct_structure(): void
    {
        $user   = User::factory()->create(['plan' => 'player_pro', 'slug' => 'test-player']);
        $player = Player::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/v1/me/player/card')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'name', 'avatar', 'slug', 'plan', 'is_pro',
                    'city', 'state', 'country', 'preferences',
                    'badges_count', 'export_enabled',
                ],
            ]);
    }
}
```
