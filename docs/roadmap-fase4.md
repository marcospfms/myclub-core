# Roadmap Fase 4 — Planos e Feature Gating

## 1. Objetivo

Implementar o modelo de plano de usuário, middleware de verificação, limites por plano no backend e o toggle de AdSense. Resolve **G1** (plano sem campo no schema).

---

## 2. Escopo

| Entrega                                        | Descrição                                                                       |
| ---------------------------------------------- | ------------------------------------------------------------------------------- |
| Migration `add_plan_to_users_table`            | Adiciona coluna `plan` enum à tabela `users`                                    |
| Enum `UserPlan`                                | Valores: `free`, `player_pro`, `club`, `liga`, `federacao`                      |
| Atualização do model `User`                    | `plan` em `$fillable`, cast, helpers `isPaid()`, `isPlanAtLeast()`, `showAds()` |
| `PlanGatingService`                            | Serviço central de verificação de limites por plano                             |
| Middleware `RequirePlan`                       | Bloqueia rotas que exigem plano mínimo                                          |
| Atualização de `TeamService::create()`         | Substitui o `TODO Fase 4` — aplica limite real para usuários Free               |
| Atualização de `ChampionshipService::create()` | Substitui o `TODO Fase 4` — aplica limite real para formato `league` Free       |
| `UserPlanController` (API)                     | Leitura do plano atual + atribuição admin                                       |
| Prop compartilhada Inertia (`show_ads`)        | Toggle de AdSense para frontend administrativo                                  |
| Testes                                         | Cobertura completa dos limites e do middleware                                  |

---

## 3. Decisões de modelagem

### 3.1 Campo vs tabela separada

O plano do usuário é armazenado **diretamente em `users.plan`** como enum. Não há tabela `subscriptions` nesta fase.

**Motivação:** nesta fase não há integração com gateway de pagamento. O campo é definido manualmente (admin) ou via fluxo de upgrade futuro. A separação em tabela própria é apropriada para quando houver billing real — deferida para fase posterior.

### 3.2 Progressão inclusiva

Os planos são inclusivos: `federacao` ⊇ `liga` ⊇ `club` ⊇ `player_pro` ⊇ `free`. O helper `isPlanAtLeast(UserPlan $min)` encapsula essa lógica e é a forma canônica de verificar permissão por plano.

### 3.3 O plano não se propaga

O plano de um usuário é individual. Um jogador do elenco de um time Club **não herda** os benefícios Club — cada usuário tem seu próprio plano.

### 3.4 Admin bypass

Administradores (`users.role = 'admin'`) têm acesso irrestrito e estão isentos de verificações de plano. O `PlanGatingService` deve verificar `$user->isAdmin()` antes de qualquer limite.

---

## 4. Migration

### `add_plan_to_users_table`

```php
Schema::table('users', function (Blueprint $table) {
    $table->enum('plan', ['free', 'player_pro', 'club', 'liga', 'federacao'])
          ->default('free')
          ->after('role');
});
```

---

## 5. Enum `UserPlan`

Localização: `app/Enums/UserPlan.php`

```php
enum UserPlan: string
{
    case Free       = 'free';
    case PlayerPro  = 'player_pro';
    case Club       = 'club';
    case Liga       = 'liga';
    case Federacao  = 'federacao';

    /**
     * Ordem numérica para comparação de hierarquia.
     */
    public function level(): int
    {
        return match($this) {
            self::Free      => 0,
            self::PlayerPro => 1,
            self::Club      => 2,
            self::Liga      => 3,
            self::Federacao => 4,
        };
    }

    public function isAtLeast(self $min): bool
    {
        return $this->level() >= $min->level();
    }
}
```

---

## 6. Model `User` — atualizações

Adicionar à classe `User` existente:

```php
// Em $fillable — acrescentar:
'plan',

// Em casts():
'plan' => UserPlan::class,

// Helpers:

/**
 * Verifica se o usuário possui pelo menos o plano informado.
 * Administradores sempre retornam true.
 */
public function isPlanAtLeast(UserPlan $min): bool
{
    return $this->isAdmin() || $this->plan->isAtLeast($min);
}

/**
 * Retorna true para planos pagos (Player Pro ou superior).
 * Administradores são considerados pagos para fins de AdSense.
 */
public function isPaid(): bool
{
    return $this->isAdmin() || $this->plan->isAtLeast(UserPlan::PlayerPro);
}

/**
 * Indica se devem ser exibidos anúncios AdSense para este usuário.
 * Planos Free exibem anúncios; planos pagos ficam isentos.
 */
public function showAds(): bool
{
    return !$this->isPaid();
}
```

---

## 7. `PlanGatingService`

Localização: `app/Services/PlanGatingService.php`

Serviço central de regras de negócio relacionadas a planos. Lança `\DomainException` quando um limite é violado — essa exceção deve ser capturada no controller e convertida em resposta HTTP 403.

```php
class PlanGatingService
{
    /**
     * Verifica se o usuário pode criar mais um time.
     * Free: máximo 1 time ativo.
     * Club/Liga/Federação: ilimitado.
     * Player Pro: não pode criar times (sem benefício de gestão de time no Player Pro).
     *
     * @throws \DomainException
     */
    public function assertCanCreateTeam(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        // Player Pro não tem benefício de criação de time
        if ($user->plan === UserPlan::PlayerPro) {
            throw new \DomainException('O plano Player Pro não inclui criação de times. Faça upgrade para Club.');
        }

        // Free: máximo 1 time ativo
        if ($user->plan === UserPlan::Free) {
            $activeTeams = Team::where('owner_id', $user->id)
                               ->where('is_active', true)
                               ->count();

            if ($activeTeams >= 1) {
                throw new \DomainException('O plano Free permite apenas 1 time ativo. Faça upgrade para Club.');
            }
        }
    }

    /**
     * Verifica se o usuário pode criar mais um campeonato no formato informado.
     * Free: máximo 1 campeonato ativo no formato `league`.
     *       Formatos `knockout` e `cup` requerem Club.
     * Club/Liga/Federação: ilimitado.
     *
     * @throws \DomainException
     */
    public function assertCanCreateChampionship(User $user, ChampionshipFormat $format): void
    {
        if ($user->isAdmin()) {
            return;
        }

        // Formatos avançados exigem Club
        if ($format !== ChampionshipFormat::League && !$user->isPlanAtLeast(UserPlan::Club)) {
            throw new \DomainException(
                'Os formatos knockout e cup são exclusivos do plano Club ou superior.'
            );
        }

        // Free: máximo 1 campeonato league ativo
        if ($user->plan === UserPlan::Free) {
            $activeLeagues = Championship::where('created_by', $user->id)
                                         ->where('format', ChampionshipFormat::League)
                                         ->whereIn('status', [
                                             ChampionshipStatus::Enrollment,
                                             ChampionshipStatus::Active,
                                         ])
                                         ->count();

            if ($activeLeagues >= 1) {
                throw new \DomainException(
                    'O plano Free permite apenas 1 campeonato league ativo. Faça upgrade para Club.'
                );
            }
        }
    }

    /**
     * Verifica se o usuário pode tornar um campeonato público.
     * Campeonatos públicos exigem plano Liga ou superior.
     *
     * @throws \DomainException
     */
    public function assertCanMakeChampionshipPublic(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if (!$user->isPlanAtLeast(UserPlan::Liga)) {
            throw new \DomainException(
                'Campeonatos públicos são exclusivos do plano Liga ou superior.'
            );
        }
    }
}
```

---

## 8. Middleware `RequirePlan`

Localização: `app/Http/Middleware/RequirePlan.php`

Middleware para rotas que exigem plano mínimo. Registrar no `bootstrap/app.php` com alias `plan`.

```php
class RequirePlan
{
    /**
     * Uso na rota: ->middleware('plan:club')
     */
    public function handle(Request $request, \Closure $next, string $minimumPlan): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Não autenticado.'], 401);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $required = UserPlan::from($minimumPlan);

        if (!$user->plan->isAtLeast($required)) {
            return response()->json([
                'success' => false,
                'message' => "Esta funcionalidade requer o plano {$required->value} ou superior.",
            ], 403);
        }

        return $next($request);
    }
}
```

Registrar em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'plan' => \App\Http\Middleware\RequirePlan::class,
    ]);
})
```

---

## 9. Atualização dos Services existentes

### 9.1 `TeamService::create()` — substituir TODO da Fase 1

Substituir o comentário `// TODO Fase 4` pela chamada real ao `PlanGatingService`:

```php
public function create(array $data, User $owner): Team
{
    $this->planGatingService->assertCanCreateTeam($owner);

    return DB::transaction(function () use ($data, $owner) {
        $team = Team::create(array_merge($data, ['owner_id' => $owner->id]));

        if (!empty($data['sport_mode_ids'])) {
            foreach ($data['sport_mode_ids'] as $sportModeId) {
                $team->sportModes()->create(['sport_mode_id' => $sportModeId]);
            }
        }

        return $team->load('sportModes.sportMode');
    });
}
```

O construtor deve receber `PlanGatingService` via injeção:

```php
public function __construct(private PlanGatingService $planGatingService) {}
```

### 9.2 `ChampionshipService::create()` — substituir TODO da Fase 3

Substituir o comentário `// TODO Fase 4` pela chamada real:

```php
public function create(array $data, User $creator): Championship
{
    $format = ChampionshipFormat::from($data['format'] ?? 'league');
    $this->planGatingService->assertCanCreateChampionship($creator, $format);

    return DB::transaction(function () use ($data, $creator) {
        // ... lógica existente ...
    });
}
```

O construtor de `ChampionshipService` deve receber adicionalmente `PlanGatingService`:

```php
public function __construct(
    private ChampionshipPhaseService $phaseService,
    private PlanGatingService $planGatingService,
) {}
```

---

## 10. API Controllers

Localização: `app/Http/Controllers/Api/`

### 10.1 `UserPlanController`

```
GET    /api/v1/me/plan                    → show   (plano do usuário autenticado)
PATCH  /api/v1/admin/users/{user}/plan    → update (admin atribui plano)
```

```php
class UserPlanController extends BaseController
{
    public function show(Request $request): JsonResponse
    {
        return $this->sendResponse([
            'plan'     => $request->user()->plan->value,
            'is_paid'  => $request->user()->isPaid(),
            'show_ads' => $request->user()->showAds(),
        ], 'Plano recuperado.');
    }

    public function update(UpdateUserPlanRequest $request, User $user): JsonResponse
    {
        $user->update(['plan' => $request->validated('plan')]);

        return $this->sendResponse([
            'plan' => $user->fresh()->plan->value,
        ], 'Plano atualizado.');
    }
}
```

### 10.2 `UpdateUserPlanRequest`

Localização: `app/Http/Requests/UpdateUserPlanRequest.php`

```php
class UpdateUserPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::enum(UserPlan::class)],
        ];
    }
}
```

---

## 11. Prop compartilhada Inertia — `show_ads`

Para o painel admin (frontend Inertia), compartilhar o toggle de ads via `HandleInertiaRequests` middleware:

Localização: `app/Http/Middleware/HandleInertiaRequests.php`

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user'     => $request->user(),
            'show_ads' => $request->user()?->showAds() ?? true,
        ],
    ]);
}
```

Tipo TypeScript correspondente (adicionar em `resources/js/types/user.d.ts`):

```ts
export type UserPlan = 'free' | 'player_pro' | 'club' | 'liga' | 'federacao';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
    plan: UserPlan;
    show_ads: boolean;
}
```

---

## 12. Rotas

```php
Route::middleware('auth:sanctum')->group(function () {

    // Plano do usuário autenticado
    Route::get('v1/me/plan', [Api\UserPlanController::class, 'show'])
        ->name('api.me.plan');

    // Admin: atribuir plano a qualquer usuário
    Route::patch('v1/admin/users/{user}/plan', [Api\UserPlanController::class, 'update'])
        ->name('api.admin.users.plan.update');

});
```

> A rota admin não usa o middleware `plan` (seria circular). A autorização é feita dentro do `UpdateUserPlanRequest::authorize()` via `isAdmin()`.

---

## 13. Testes

Localização: `tests/Feature/Api/`

### 13.1 `PlanGatingTest`

```php
class PlanGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_blocked_on_second_team(): void
    {
        $user      = User::factory()->create(['plan' => 'free']);
        $sportMode = SportMode::factory()->create();
        Team::factory()->create(['owner_id' => $user->id, 'is_active' => true]);

        $this->actingAs($user)->postJson('/api/v1/teams', [
            'name'           => 'Segundo Time',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertStatus(422); // DomainException convertida para 422 pelo handler
    }

    public function test_club_user_can_create_multiple_teams(): void
    {
        $user      = User::factory()->create(['plan' => 'club']);
        $sportMode = SportMode::factory()->create();
        Team::factory()->create(['owner_id' => $user->id, 'is_active' => true]);

        $this->actingAs($user)->postJson('/api/v1/teams', [
            'name'           => 'Segundo Time',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertCreated();
    }

    public function test_player_pro_cannot_create_team(): void
    {
        $user      = User::factory()->create(['plan' => 'player_pro']);
        $sportMode = SportMode::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/teams', [
            'name'           => 'Meu Time',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertStatus(422);
    }

    public function test_admin_bypasses_plan_limits(): void
    {
        $admin     = User::factory()->create(['role' => 'admin', 'plan' => 'free']);
        $sportMode = SportMode::factory()->create();
        Team::factory()->create(['owner_id' => $admin->id, 'is_active' => true]);

        $this->actingAs($admin)->postJson('/api/v1/teams', [
            'name'           => 'Time Admin',
            'sport_mode_ids' => [$sportMode->id],
        ])->assertCreated();
    }

    public function test_free_user_blocked_on_second_active_league(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        Championship::factory()->create([
            'created_by' => $user->id,
            'format'     => 'league',
            'status'     => 'active',
        ]);

        $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'   => 'Liga 2',
            'format' => 'league',
        ])->assertStatus(422);
    }

    public function test_free_user_cannot_create_knockout_championship(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'   => 'Copa',
            'format' => 'knockout',
        ])->assertStatus(422);
    }
}
```

### 13.2 `RequirePlanMiddlewareTest`

```php
class RequirePlanMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_blocks_free_user_on_club_route(): void
    {
        // Rota hipotética protegida por ->middleware('plan:club')
        // Testar que o middleware retorna 403 para usuário Free
        $user = User::factory()->create(['plan' => 'free']);

        $response = $this->actingAs($user)
            ->withMiddleware([RequirePlan::class . ':club'])
            ->getJson('/api/v1/some-club-feature');

        $response->assertForbidden();
    }

    public function test_middleware_allows_club_user(): void
    {
        $user = User::factory()->create(['plan' => 'club']);

        $response = $this->actingAs($user)
            ->withMiddleware([RequirePlan::class . ':club'])
            ->getJson('/api/v1/some-club-feature');

        // Não é 403 (pode ser 404 se a rota não existir — apenas verificar que não é 403)
        $response->assertStatus(fn ($status) => $status !== 403);
    }

    public function test_admin_bypasses_plan_middleware(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'plan' => 'free']);

        // Admin com plano Free deve passar pelo middleware plan:liga
        $response = $this->actingAs($admin)
            ->withMiddleware([RequirePlan::class . ':liga'])
            ->getJson('/api/v1/some-liga-feature');

        $response->assertStatus(fn ($status) => $status !== 403);
    }
}
```

### 13.3 `UserPlanControllerTest`

```php
class UserPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_plan(): void
    {
        $user = User::factory()->create(['plan' => 'club']);

        $this->actingAs($user)->getJson('/api/v1/me/plan')
            ->assertOk()
            ->assertJsonPath('data.plan', 'club')
            ->assertJsonPath('data.is_paid', true)
            ->assertJsonPath('data.show_ads', false);
    }

    public function test_free_user_shows_ads(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)->getJson('/api/v1/me/plan')
            ->assertOk()
            ->assertJsonPath('data.show_ads', true);
    }

    public function test_admin_can_update_user_plan(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['plan' => 'free']);

        $this->actingAs($admin)->patchJson("/api/v1/admin/users/{$target->id}/plan", [
            'plan' => 'club',
        ])->assertOk()->assertJsonPath('data.plan', 'club');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'plan' => 'club']);
    }

    public function test_non_admin_cannot_update_user_plan(): void
    {
        $user   = User::factory()->create(['plan' => 'club']);
        $target = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)->patchJson("/api/v1/admin/users/{$target->id}/plan", [
            'plan' => 'liga',
        ])->assertForbidden();
    }

    public function test_plan_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/me/plan')->assertUnauthorized();
    }
}
```

---

## 14. Tratamento de `DomainException` no handler global

O `PlanGatingService` lança `\DomainException`. Para que os controllers não precisem de try/catch, registrar no handler global em `bootstrap/app.php`:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\DomainException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    });
})
```

> Usar **422 Unprocessable Content** para violações de regra de negócio (plano insuficiente, limite atingido), diferente de 403 que indica falta de permissão de autorização. Esse comportamento já é consistente com o padrão adotado nas Fases 2 e 3.

---

## 15. Checklist de Conclusão

### Banco

- [ ] Migration `add_plan_to_users_table`
- [ ] `php artisan migrate`

### Enums e Models

- [ ] Enum `UserPlan`
- [ ] Model `User` — adicionar `plan` ao `$fillable`, cast, helpers `isPlanAtLeast`, `isPaid`, `showAds`

### Backend

- [ ] `PlanGatingService` (`assertCanCreateTeam`, `assertCanCreateChampionship`, `assertCanMakeChampionshipPublic`)
- [ ] Middleware `RequirePlan` registrado com alias `plan` em `bootstrap/app.php`
- [ ] `TeamService::create()` — substituir `TODO Fase 4` pela chamada ao `PlanGatingService`
- [ ] `ChampionshipService::create()` — substituir `TODO Fase 4` pela chamada ao `PlanGatingService`
- [ ] `UpdateUserPlanRequest`
- [ ] `UserPlanController`
- [ ] Rotas registradas em `routes/api.php`
- [ ] `DomainException` handler global em `bootstrap/app.php`

### Frontend

- [ ] `show_ads` compartilhado via `HandleInertiaRequests`
- [ ] Tipo `AuthUser` e `UserPlan` em `resources/js/types/user.d.ts`

### Testes

- [ ] `PlanGatingTest`
- [ ] `RequirePlanMiddlewareTest`
- [ ] `UserPlanControllerTest`
- [ ] Todos os testes passando (`php artisan test`)

---

## 16. Comandos de Referência

```bash
# Migration
php artisan make:migration add_plan_to_users_table

php artisan migrate

# Enum
# app/Enums/UserPlan.php (criar manualmente)

# Service
# app/Services/PlanGatingService.php (criar manualmente)

# Middleware
php artisan make:middleware RequirePlan

# Request
php artisan make:request UpdateUserPlanRequest

# Controller
php artisan make:controller Api/UserPlanController

# Testes
php artisan test
php artisan test --filter=PlanGatingTest
php artisan test --filter=RequirePlanMiddlewareTest
php artisan test --filter=UserPlanControllerTest
```

---

## 17. Notas relevantes para fases futuras

- **Fase 5 (Player Pro e Descoberta):** URL amigável (`@slug`) e cartão digital exportável — verificar via `$user->isPlanAtLeast(UserPlan::PlayerPro)`. O `PlanGatingService` deve receber novos métodos `assertCanUseSlug()` e `assertCanExportCard()`.
- **Fase 8 (Liga: campeonatos públicos):** `assertCanMakeChampionshipPublic()` já está implementado nesta fase — basta chamá-lo em `ChampionshipService::update()` quando `is_public = true`.
- **Futuramente (billing):** quando houver integração com gateway de pagamento, criar tabela `subscriptions` e atualizar o campo `users.plan` via webhook. A lógica do `PlanGatingService` permanece intacta — só muda como o campo `plan` é atualizado.
