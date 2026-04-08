# Roadmap Fase 0 — Fundação

> Detalhamento completo de implementação da Fase 0. Cobertura: migrations, seeders, models, services, form requests, resources, controllers, rotas, frontend Vue/Inertia, types TypeScript e testes.
>
> Referências de schema: `docs/database/schema.md` §2.
> Referências de padrões: `docs/patterns/`.

---

## 1. Escopo

| Item                                                       | Status         |
| ---------------------------------------------------------- | -------------- |
| Estrutura base (Laravel 13, Sanctum, Fortify, Inertia+Vue) | ✅ Concluído   |
| Migrations — 6 tabelas de catálogo                         | ✅ Concluído   |
| Migrations — 3 tabelas pivô de catálogo                    | ✅ Concluído   |
| Seeders com dados de referência                            | ✅ Concluído   |
| Models                                                     | ⬜ Pendente    |
| Services                                                   | ⬜ Pendente    |
| Form Requests                                              | ⬜ Pendente    |
| API Resources                                              | ⬜ Pendente    |
| API Controllers (selects autenticados — leitura de catálogo) | ⬜ Pendente    |
| Admin Controllers (CRUD via Inertia)                       | ⬜ Pendente    |
| Rotas API e Admin                                          | ⬜ Pendente    |
| Páginas Vue/Inertia — painel admin                         | ⬜ Pendente    |
| Types TypeScript                                           | ⬜ Pendente    |
| Testes Feature (Admin CRUD + API leitura)                  | ⬜ Pendente    |

---

## 2. Infraestrutura Base (✅ Concluída)

| Componente                           | Versão |
| ------------------------------------ | ------ |
| Laravel                              | v13    |
| PHP                                  | 8.4    |
| Inertia.js + Vue 3                   | v3     |
| TailwindCSS                          | v4     |
| Sanctum                              | v4     |
| Fortify                              | v1     |
| Wayfinder (typed route functions)    | v0     |
| PHPUnit                              | v12    |

Nenhuma ação necessária nesta parte.

---

## 3. Entidades de Catálogo

São tabelas de baixa volatilidade gerenciadas pelo admin interno. Não são criadas por usuários finais.

| Entidade   | Tabela        | Pivôs de vínculo   |
| ---------- | ------------- | ------------------ |
| SportMode  | `sport_modes` | category, formation, position |
| Category   | `categories`  | —                  |
| Position   | `positions`   | —                  |
| Formation  | `formations`  | —                  |
| StaffRole  | `staff_roles` | —                  |
| BadgeType  | `badge_types` | —                  |

---

## 4. Migrations

Ordem de criação — principais primeiro, pivôs depois.

### 4.1 `create_sport_modes_table`

```php
Schema::create('sport_modes', function (Blueprint $table) {
    $table->id();
    $table->string('key', 60)->unique();
    $table->string('name', 45);
    $table->timestamps();
});
```

### 4.2 `create_categories_table`

```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('key', 60)->unique();
    $table->string('name', 45);
    $table->timestamps();
});
```

### 4.3 `create_positions_table`

```php
Schema::create('positions', function (Blueprint $table) {
    $table->id();
    $table->string('key', 60)->unique();
    $table->string('name', 45);
    $table->char('abbreviation', 3);
    $table->timestamps();
});
```

### 4.4 `create_formations_table`

```php
Schema::create('formations', function (Blueprint $table) {
    $table->id();
    $table->string('key', 30)->unique();
    $table->string('name', 15);
    $table->timestamps();
});
```

### 4.5 `create_staff_roles_table`

```php
Schema::create('staff_roles', function (Blueprint $table) {
    $table->id();
    $table->string('name', 60)->unique(); // name já é o slug-chave (ex: head_coach)
    $table->timestamps();
});
```

### 4.6 `create_badge_types_table`

```php
Schema::create('badge_types', function (Blueprint $table) {
    $table->id();
    $table->string('name', 60)->unique(); // name já é o slug-chave (ex: golden_ball)
    $table->string('label', 100);         // label é o nome exibido (ex: Bola de Ouro)
    $table->text('description')->nullable();
    $table->string('icon', 100)->nullable();
    $table->enum('scope', ['championship', 'friendly', 'career', 'seasonal']);
    $table->timestamps();
});
```

### 4.7 `create_sport_mode_category_table` (pivô)

```php
Schema::create('sport_mode_category', function (Blueprint $table) {
    $table->foreignId('sport_mode_id')->constrained()->cascadeOnDelete();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->primary(['sport_mode_id', 'category_id']);
    $table->timestamps();
});
```

### 4.8 `create_sport_mode_formation_table` (pivô)

```php
Schema::create('sport_mode_formation', function (Blueprint $table) {
    $table->foreignId('sport_mode_id')->constrained()->cascadeOnDelete();
    $table->foreignId('formation_id')->constrained()->cascadeOnDelete();
    $table->primary(['sport_mode_id', 'formation_id']);
    $table->timestamps();
});
```

### 4.9 `create_sport_mode_position_table` (pivô)

```php
Schema::create('sport_mode_position', function (Blueprint $table) {
    $table->foreignId('sport_mode_id')->constrained()->cascadeOnDelete();
    $table->foreignId('position_id')->constrained()->cascadeOnDelete();
    $table->primary(['sport_mode_id', 'position_id']);
    $table->timestamps();
});
```

---

## 5. Seeders

### 5.1 `SportModeSeeder`

```php
SportMode::insert([
    ['key' => 'campo',   'name' => 'Campo',   'created_at' => now(), 'updated_at' => now()],
    ['key' => 'quadra',  'name' => 'Quadra',  'created_at' => now(), 'updated_at' => now()],
    ['key' => 'society', 'name' => 'Society', 'created_at' => now(), 'updated_at' => now()],
    ['key' => 'areia',   'name' => 'Areia',   'created_at' => now(), 'updated_at' => now()],
]);
```

### 5.2 `CategorySeeder`

```php
Category::insert([
    ['key' => 'livre',  'name' => 'Livre',  'created_at' => now(), 'updated_at' => now()],
    ['key' => 'sub_15', 'name' => 'Sub-15', 'created_at' => now(), 'updated_at' => now()],
    ['key' => 'sub_17', 'name' => 'Sub-17', 'created_at' => now(), 'updated_at' => now()],
    ['key' => 'sub_20', 'name' => 'Sub-20', 'created_at' => now(), 'updated_at' => now()],
]);
```

### 5.3 `PositionSeeder`

```php
Position::insert([
    ['key' => 'goleiro',              'name' => 'Goleiro',              'abbreviation' => 'GOL'],
    ['key' => 'zagueiro',             'name' => 'Zagueiro',             'abbreviation' => 'ZC'],
    ['key' => 'lateral_direito',      'name' => 'Lateral Direito',      'abbreviation' => 'LD'],
    ['key' => 'lateral_esquerdo',     'name' => 'Lateral Esquerdo',     'abbreviation' => 'LE'],
    ['key' => 'volante',              'name' => 'Volante',              'abbreviation' => 'VOL'],
    ['key' => 'meia_ligacao',         'name' => 'Meia de Ligação',      'abbreviation' => 'ML'],
    ['key' => 'meia_lateral_direito', 'name' => 'Meia Lateral Direito', 'abbreviation' => 'MLD'],
    ['key' => 'meia_lateral_esquerdo','name' => 'Meia Lateral Esquerdo','abbreviation' => 'MLE'],
    ['key' => 'meia_atacante',        'name' => 'Meia Atacante',        'abbreviation' => 'MAT'],
    ['key' => 'segundo_atacante',     'name' => 'Segundo Atacante',     'abbreviation' => 'SA'],
    ['key' => 'ponta_direita',        'name' => 'Ponta Direita',        'abbreviation' => 'PD'],
    ['key' => 'ponta_esquerda',       'name' => 'Ponta Esquerda',       'abbreviation' => 'PE'],
    ['key' => 'atacante',             'name' => 'Atacante',             'abbreviation' => 'ATA'],
    ['key' => 'fixo',                 'name' => 'Fixo',                 'abbreviation' => 'FIX'],
    ['key' => 'ala_direito',          'name' => 'Ala Direito',          'abbreviation' => 'ALD'],
    ['key' => 'ala_esquerdo',         'name' => 'Ala Esquerdo',         'abbreviation' => 'ALE'],
    ['key' => 'pivo',                 'name' => 'Pivô',                 'abbreviation' => 'PIV'],
]);
// timestamps implícitos via created_at/updated_at = now() em cada linha
```

> Posições de campo (GOL→ATA) são associadas a Campo e Society.
> Posições de futsal (GOL, FIX, ALD, ALE, PIV) são associadas a Quadra e Areia.

### 5.4 `FormationSeeder`

```php
Formation::insert([
    ['key' => '4-4-2', 'name' => '4-4-2'],
    ['key' => '4-3-3', 'name' => '4-3-3'],
    ['key' => '4-5-1', 'name' => '4-5-1'],
    ['key' => '3-5-2', 'name' => '3-5-2'],
    ['key' => '3-4-3', 'name' => '3-4-3'],
    ['key' => '3-6-1', 'name' => '3-6-1'],
    ['key' => '1-2-1', 'name' => '1-2-1'],
    ['key' => '2-2-1', 'name' => '2-2-1'],
]);
```

> Formações de campo (4-4-2 a 3-6-1) → Campo e Society.
> Formações de futsal (1-2-1, 2-2-1) → Quadra e Areia.

### 5.5 `StaffRoleSeeder`

```php
StaffRole::insert([
    ['name' => 'head_coach'],
    ['name' => 'assistant_coach'],
    ['name' => 'physical_trainer'],
    ['name' => 'goalkeeping_coach'],
    ['name' => 'scout'],
    ['name' => 'analyst'],
    ['name' => 'physiotherapist'],
    ['name' => 'doctor'],
    ['name' => 'other'],
]);
```

### 5.6 `BadgeTypeSeeder`

```php
$badges = [
    ['name' => 'golden_ball',        'label' => 'Bola de Ouro',            'scope' => 'championship', 'description' => 'Mais votos de MVP num campeonato'],
    ['name' => 'top_scorer',         'label' => 'Artilheiro',              'scope' => 'championship', 'description' => 'Maior número de gols num campeonato'],
    ['name' => 'best_assist',        'label' => 'Garçom',                  'scope' => 'championship', 'description' => 'Maior número de assistências num campeonato'],
    ['name' => 'best_goalkeeper',    'label' => 'Melhor Goleiro',          'scope' => 'championship', 'description' => 'Goleiro com menor média de gols sofridos'],
    ['name' => 'fair_play',          'label' => 'Fair Play',               'scope' => 'championship', 'description' => 'Zero cartões durante todo o campeonato'],
    ['name' => 'hat_trick',          'label' => 'Hat-trick',               'scope' => 'career',       'description' => 'Marcou 3+ gols em uma única partida'],
    ['name' => 'iron_man',           'label' => 'Homem de Ferro',          'scope' => 'championship', 'description' => 'Participou de 100% das partidas do campeonato'],
    ['name' => 'unbeaten_champion',  'label' => 'Campeão Invicto',         'scope' => 'championship', 'description' => 'Conquistou o título sem perder nenhuma partida'],
    ['name' => 'top_scorer_season',  'label' => 'Artilheiro da Temporada', 'scope' => 'seasonal',     'description' => 'Maior total de gols na temporada'],
    ['name' => 'best_assist_season', 'label' => 'Garçom da Temporada',     'scope' => 'seasonal',     'description' => 'Maior total de assistências na temporada'],
    ['name' => 'mvp_streak',         'label' => 'MVP em Série',            'scope' => 'career',       'description' => 'Ganhou MVP em 3 ou mais partidas consecutivas'],
    ['name' => 'loyal_player',       'label' => 'Jogador Fiel',            'scope' => 'career',       'description' => 'Participou de 5+ campeonatos pelo mesmo time'],
    ['name' => 'rising_star',        'label' => 'Estrela em Ascensão',     'scope' => 'seasonal',     'description' => 'Destaque de desempenho na primeira temporada completa'],
    ['name' => 'clean_sweep',        'label' => 'Varredura',               'scope' => 'championship', 'description' => 'Venceu todas as partidas da fase de grupos'],
];
BadgeType::insert(array_map(fn ($b) => array_merge($b, ['created_at' => now(), 'updated_at' => now()]), $badges));
```

### 5.7 `SportModeCategorySeeder`

Todas as categorias estão disponíveis em todas as modalidades.

```php
$sportModeIds = SportMode::pluck('id');
$categoryIds  = Category::pluck('id');

$rows = [];
foreach ($sportModeIds as $sm) {
    foreach ($categoryIds as $cat) {
        $rows[] = ['sport_mode_id' => $sm, 'category_id' => $cat, 'created_at' => now(), 'updated_at' => now()];
    }
}
DB::table('sport_mode_category')->insert($rows);
```

### 5.8 `SportModeFormationSeeder`

```php
$campo   = SportMode::where('key', 'campo')->value('id');
$society = SportMode::where('key', 'society')->value('id');
$quadra  = SportMode::where('key', 'quadra')->value('id');
$areia   = SportMode::where('key', 'areia')->value('id');

$field   = Formation::whereIn('key', ['4-4-2','4-3-3','4-5-1','3-5-2','3-4-3','3-6-1'])->pluck('id');
$futsal  = Formation::whereIn('key', ['1-2-1','2-2-1'])->pluck('id');

// campo + society → formações de campo
// quadra + areia → formações de futsal
```

### 5.9 `SportModePositionSeeder`

```php
$fieldAbbr  = ['GOL','ZC','LD','LE','VOL','ML','MLD','MLE','MAT','SA','PD','PE','ATA'];
$futsalAbbr = ['GOL','FIX','ALD','ALE','PIV'];

$fieldPositions  = Position::whereIn('abbreviation', $fieldAbbr)->pluck('id');
$futsalPositions = Position::whereIn('abbreviation', $futsalAbbr)->pluck('id');

// campo + society → posições de campo
// quadra + areia → posições de futsal
```

### 5.10 `DatabaseSeeder` — ordem de chamada

```php
$this->call([
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
```

---

## 6. Models

Localização: `app/Models/`

### 6.1 `SportMode`

```php
class SportMode extends Model
{
    protected $table = 'sport_modes';

    protected $fillable = ['key', 'name'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'sport_mode_category')->withTimestamps();
    }

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(Formation::class, 'sport_mode_formation')->withTimestamps();
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, 'sport_mode_position')->withTimestamps();
    }
}
```

### 6.2 `Category`

```php
class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['key', 'name'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_category')->withTimestamps();
    }
}
```

### 6.3 `Position`

```php
class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = ['key', 'name', 'abbreviation'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_position')->withTimestamps();
    }
}
```

### 6.4 `Formation`

```php
class Formation extends Model
{
    protected $table = 'formations';

    protected $fillable = ['key', 'name'];

    public function sportModes(): BelongsToMany
    {
        return $this->belongsToMany(SportMode::class, 'sport_mode_formation')->withTimestamps();
    }
}
```

### 6.5 `StaffRole`

```php
class StaffRole extends Model
{
    protected $table = 'staff_roles';

    protected $fillable = ['name'];
}
```

### 6.6 `BadgeType`

```php
class BadgeType extends Model
{
    protected $table = 'badge_types';

    protected $fillable = ['name', 'label', 'description', 'icon', 'scope'];

    protected function casts(): array
    {
        return [
            'scope' => BadgeScope::class, // enum backed
        ];
    }
}
```

> Criar enum `app/Enums/BadgeScope.php` com cases: `Championship`, `Friendly`, `Career`, `Seasonal`.

---

## 7. Enums

`app/Enums/BadgeScope.php`:

```php
enum BadgeScope: string
{
    case Championship = 'championship';
    case Friendly     = 'friendly';
    case Career       = 'career';
    case Seasonal     = 'seasonal';
}
```

---

## 8. Services

Localização: `app/Services/Catalog/`

Um service por entidade. Cada service expõe os métodos usados pelos controllers (admin + API).

### Estrutura padrão (exemplo: `SportModeService`)

```php
namespace App\Services\Catalog;

class SportModeService
{
    public function listAll(): Collection
    {
        return SportMode::with(['categories', 'formations', 'positions'])->orderBy('name')->get();
    }

    public function create(array $data): SportMode
    {
        return SportMode::create($data);
    }

    public function update(SportMode $sportMode, array $data): SportMode
    {
        $sportMode->update($data);
        return $sportMode->fresh();
    }

    public function delete(SportMode $sportMode): void
    {
        $sportMode->delete();
    }

    public function syncCategories(SportMode $sportMode, array $categoryIds): void
    {
        $sportMode->categories()->sync($categoryIds);
    }

    public function syncFormations(SportMode $sportMode, array $formationIds): void
    {
        $sportMode->formations()->sync($formationIds);
    }

    public function syncPositions(SportMode $sportMode, array $positionIds): void
    {
        $sportMode->positions()->sync($positionIds);
    }
}
```

Services a criar:
- `SportModeService`
- `CategoryService`
- `PositionService`
- `FormationService`
- `StaffRoleService`
- `BadgeTypeService`

Todos os demais services (`CategoryService`, `PositionService`, `FormationService`, `StaffRoleService`, `BadgeTypeService`) têm apenas `listAll`, `create`, `update`, `delete`. Nenhuma lógica de sync de pivô — só SportMode tem pivôs gerenciados.

---

## 9. Form Requests

Localização: `app/Http/Requests/Catalog/`

### Store requests

| Classe                    | Regras de validação principais                                                               |
| ------------------------- | -------------------------------------------------------------------------------------------- |
| `StoreSportModeRequest`   | `key`: required, alpha_dash, max:60, unique:sport_modes \| `name`: required, string, max:45  |
| `StoreCategoryRequest`    | `key`: required, alpha_dash, max:60, unique:categories \| `name`: required, string, max:45   |
| `StorePositionRequest`    | `key`: required, alpha_dash, max:60, unique:positions \| `name`: required, string, max:45 \| `abbreviation`: required, string, size:3 |
| `StoreFormationRequest`   | `key`: required, max:30, unique:formations \| `name`: required, string, max:15              |
| `StoreStaffRoleRequest`   | `name`: required, alpha_dash, max:60, unique:staff_roles _(name é o slug)_                  |
| `StoreBadgeTypeRequest`   | `name`: required, alpha_dash, max:60, unique:badge_types _(name é o slug)_ \| `label`: required, string, max:100 \| `scope`: required, in:championship,friendly,career,seasonal |

### Update requests

Mesmas regras, mas `unique` com except no ID atual:

```php
'name' => ['required', 'string', 'max:45', Rule::unique('sport_modes')->ignore($this->sport_mode)],
```

> Update requests herdam das Store — só sobrescrevem a regra `unique`.

### `UpdateSportModeRequest` (exemplo completo)

```php
class UpdateSportModeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'key'           => ['required', 'alpha_dash', 'max:60', Rule::unique('sport_modes')->ignore($this->sport_mode)],
            'name'          => ['required', 'string', 'max:45'],
            'category_ids'  => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'formation_ids' => ['sometimes', 'array'],
            'formation_ids.*' => ['integer', 'exists:formations,id'],
            'position_ids'  => ['sometimes', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
        ];
    }
}
```

---

## 10. API Resources

Localização: `app/Http/Resources/Catalog/`

Todos os campos em `snake_case`. Relacionamentos com `whenLoaded()`.

### `SportModeResource`

```php
class SportModeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'key'        => $this->key,
            'name'       => $this->name,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'formations' => FormationResource::collection($this->whenLoaded('formations')),
            'positions'  => PositionResource::collection($this->whenLoaded('positions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Outros Resources (estrutura mínima)

```php
// CategoryResource
['id' => $this->id, 'key' => $this->key, 'name' => $this->name]

// PositionResource
['id' => $this->id, 'key' => $this->key, 'name' => $this->name, 'abbreviation' => $this->abbreviation]

// FormationResource
['id' => $this->id, 'key' => $this->key, 'name' => $this->name]

// StaffRoleResource  (name é o slug-chave)
['id' => $this->id, 'name' => $this->name]

// BadgeTypeResource  (name é o slug-chave, label é o nome exibido)
['id' => $this->id, 'name' => $this->name, 'label' => $this->label,
 'description' => $this->description, 'icon' => $this->icon, 'scope' => $this->scope]
```

---

## 11. API Controllers

Localização: `app/Http/Controllers/Api/Catalog/`

A API de catálogo expõe **apenas leitura** (`GET index`). É consumida pelo frontend autenticado para popular selects (posições, formações, categorias, etc.) em formulários de times, campeonatos e convites. Escrita é exclusivamente via admin web (Inertia). Requer `auth:sanctum`.

Todos estendem `BaseController` e retornam via `sendResponse()`.

### `SportModeController` (API)

```php
namespace App\Http\Controllers\Api\Catalog;

class SportModeController extends BaseController
{
    public function __construct(private SportModeService $sportModeService) {}

    public function index(): JsonResponse
    {
        $sportModes = $this->sportModeService->listAll();
        return $this->sendResponse(SportModeResource::collection($sportModes), 'Sport modes retrieved.');
    }
}
```

> Repetir o mesmo padrão para `CategoryController`, `PositionController`, `FormationController`, `StaffRoleController`, `BadgeTypeController`.

---

## 12. Admin Controllers

Localização: `app/Http/Controllers/Admin/Catalog/`

Não estendem `BaseController`. Retornam `Inertia::render()` ou `redirect()`.

### `SportModeController` (Admin)

```php
namespace App\Http\Controllers\Admin\Catalog;

use Inertia\Inertia;
use Inertia\Response;

class SportModeController extends Controller
{
    public function __construct(private SportModeService $sportModeService) {}

    public function index(): Response
    {
        return Inertia::render('admin/catalog/sport-modes/Index', [
            'sportModes' => SportModeResource::collection(
                $this->sportModeService->listAll()
            ),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/catalog/sport-modes/Create', [
            'categories' => CategoryResource::collection(Category::all()),
            'formations' => FormationResource::collection(Formation::all()),
            'positions'  => PositionResource::collection(Position::all()),
        ]);
    }

    public function store(StoreSportModeRequest $request): RedirectResponse
    {
        $sportMode = $this->sportModeService->create($request->validated());

        if ($request->filled('category_ids')) {
            $this->sportModeService->syncCategories($sportMode, $request->category_ids);
        }
        if ($request->filled('formation_ids')) {
            $this->sportModeService->syncFormations($sportMode, $request->formation_ids);
        }
        if ($request->filled('position_ids')) {
            $this->sportModeService->syncPositions($sportMode, $request->position_ids);
        }

        return redirect()->route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade criada com sucesso.');
    }

    public function edit(SportMode $sportMode): Response
    {
        $sportMode->load(['categories', 'formations', 'positions']);

        return Inertia::render('admin/catalog/sport-modes/Edit', [
            'sportMode'  => new SportModeResource($sportMode),
            'categories' => CategoryResource::collection(Category::all()),
            'formations' => FormationResource::collection(Formation::all()),
            'positions'  => PositionResource::collection(Position::all()),
        ]);
    }

    public function update(UpdateSportModeRequest $request, SportMode $sportMode): RedirectResponse
    {
        $this->sportModeService->update($sportMode, $request->only('name'));
        $this->sportModeService->syncCategories($sportMode, $request->category_ids ?? []);
        $this->sportModeService->syncFormations($sportMode, $request->formation_ids ?? []);
        $this->sportModeService->syncPositions($sportMode, $request->position_ids ?? []);

        return redirect()->route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade atualizada com sucesso.');
    }

    public function destroy(SportMode $sportMode): RedirectResponse
    {
        $this->sportModeService->delete($sportMode);

        return redirect()->route('admin.catalog.sport-modes.index')
            ->with('success', 'Modalidade removida.');
    }
}
```

> Os demais controllers de catálogo (`CategoryController`, `PositionController`, `FormationController`, `StaffRoleController`, `BadgeTypeController`) seguem o mesmo padrão sem as chamadas de sync de pivô.

---

## 13. Rotas

### `routes/api.php`

```php
Route::middleware('auth:sanctum')->prefix('v1/catalog')->name('api.catalog.')->group(function () {
    Route::get('sport-modes', [Api\Catalog\SportModeController::class, 'index'])->name('sport-modes.index');
    Route::get('categories',  [Api\Catalog\CategoryController::class, 'index'])->name('categories.index');
    Route::get('positions',   [Api\Catalog\PositionController::class, 'index'])->name('positions.index');
    Route::get('formations',  [Api\Catalog\FormationController::class, 'index'])->name('formations.index');
    Route::get('staff-roles', [Api\Catalog\StaffRoleController::class, 'index'])->name('staff-roles.index');
    Route::get('badge-types', [Api\Catalog\BadgeTypeController::class, 'index'])->name('badge-types.index');
});
```

> Requer `auth:sanctum`. Estes endpoints são consumidos pelo frontend autenticado para popular selects em formulários.

### `routes/admin.php`

```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {

    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::resource('sport-modes', Admin\Catalog\SportModeController::class)
            ->except(['show']);
        Route::resource('categories', Admin\Catalog\CategoryController::class)
            ->except(['show']);
        Route::resource('positions', Admin\Catalog\PositionController::class)
            ->except(['show']);
        Route::resource('formations', Admin\Catalog\FormationController::class)
            ->except(['show']);
        Route::resource('staff-roles', Admin\Catalog\StaffRoleController::class)
            ->except(['show']);
        Route::resource('badge-types', Admin\Catalog\BadgeTypeController::class)
            ->except(['show']);
    });

});
```

> O middleware `auth` protege todas as rotas admin. Adicionar um middleware de `role:admin` quando a política de autorização for implementada na Fase 4.

---

## 14. Frontend Admin

### 14.1 Estrutura de arquivos

```
resources/js/
├── pages/
│   └── admin/
│       └── catalog/
│           ├── sport-modes/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Partials/
│           │       └── SportModeForm.vue
│           ├── categories/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Partials/
│           │       └── CategoryForm.vue
│           ├── positions/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Partials/
│           │       └── PositionForm.vue
│           ├── formations/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Edit.vue
│           ├── staff-roles/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Edit.vue
│           └── badge-types/
│               ├── Index.vue
│               ├── Create.vue
│               ├── Edit.vue
│               └── Partials/
│                   └── BadgeTypeForm.vue
├── types/
│   └── catalog/
│       ├── sport-mode.d.ts
│       ├── category.d.ts
│       ├── position.d.ts
│       ├── formation.d.ts
│       ├── staff-role.d.ts
│       └── badge-type.d.ts
└── components/
    └── catalog/
        └── DeleteConfirmModal.vue
```

### 14.2 TypeScript Types

`resources/js/types/catalog/sport-mode.d.ts`:

```ts
export interface SportMode {
  id: number
  name: string
  categories: Category[]
  formations: Formation[]
  positions: Position[]
  created_at: string
  updated_at: string
}
```

`resources/js/types/catalog/position.d.ts`:

```ts
export interface Position {
  id: number
  name: string
  abbreviation: string
  created_at: string
  updated_at: string
}
```

`resources/js/types/catalog/badge-type.d.ts`:

```ts
export type BadgeScope = 'championship' | 'friendly' | 'career' | 'seasonal'

export interface BadgeType {
  id: number
  name: string
  label: string
  description: string | null
  icon: string | null
  scope: BadgeScope
  created_at: string
  updated_at: string
}
```

> Os demais types (`Category`, `Formation`, `StaffRole`) têm apenas `id`, `name`, `created_at`, `updated_at`.

### 14.3 Telas por entidade

#### Sport Modes — Index.vue

```vue
<script setup lang="ts">
import type { SportMode } from '@/types/catalog/sport-mode'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  sportModes: { data: SportMode[] }
}>()

function destroy(id: number) {
  if (confirm('Excluir esta modalidade?')) {
    router.delete(route('admin.catalog.sport-modes.destroy', id))
  }
}
</script>

<template>
  <div>
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Modalidades Esportivas</h1>
      <Link :href="route('admin.catalog.sport-modes.create')" class="btn btn-primary">
        Nova Modalidade
      </Link>
    </div>

    <table class="mt-4 w-full text-sm">
      <thead>
        <tr>
          <th class="text-left">Nome</th>
          <th class="text-left">Categorias</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="sm in sportModes.data" :key="sm.id">
          <td>{{ sm.name }}</td>
          <td>{{ sm.categories.map(c => c.name).join(', ') }}</td>
          <td class="flex gap-2">
            <Link :href="route('admin.catalog.sport-modes.edit', sm.id)">Editar</Link>
            <button @click="destroy(sm.id)">Excluir</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

#### Sport Modes — Partials/SportModeForm.vue

```vue
<script setup lang="ts">
import type { Category } from '@/types/catalog/category'
import type { Formation } from '@/types/catalog/formation'
import type { Position } from '@/types/catalog/position'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  categories: Category[]
  formations: Formation[]
  positions: Position[]
  initialData?: {
    name: string
    category_ids: number[]
    formation_ids: number[]
    position_ids: number[]
  }
  action: string
  method?: 'post' | 'put'
}>()

const form = useForm({
  name:         props.initialData?.name ?? '',
  category_ids: props.initialData?.category_ids ?? [],
  formation_ids: props.initialData?.formation_ids ?? [],
  position_ids:  props.initialData?.position_ids ?? [],
})

function submit() {
  if (props.method === 'put') {
    form.put(props.action)
  } else {
    form.post(props.action)
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">Nome</label>
      <input v-model="form.name" type="text" class="input" />
      <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
    </div>

    <div>
      <label class="block text-sm font-medium">Categorias disponíveis</label>
      <div v-for="cat in categories" :key="cat.id" class="flex items-center gap-2">
        <input type="checkbox" :value="cat.id" v-model="form.category_ids" />
        <span>{{ cat.name }}</span>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Formações disponíveis</label>
      <div v-for="fm in formations" :key="fm.id" class="flex items-center gap-2">
        <input type="checkbox" :value="fm.id" v-model="form.formation_ids" />
        <span>{{ fm.name }}</span>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium">Posições disponíveis</label>
      <div v-for="pos in positions" :key="pos.id" class="flex items-center gap-2">
        <input type="checkbox" :value="pos.id" v-model="form.position_ids" />
        <span>{{ pos.abbreviation }} — {{ pos.name }}</span>
      </div>
    </div>

    <button type="submit" :disabled="form.processing" class="btn btn-primary">
      Salvar
    </button>
  </form>
</template>
```

#### Entidades simples (Category, Formation, StaffRole) — Form Partial

Formulário mínimo com apenas o campo `name`:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  initialName?: string
  action: string
  method?: 'post' | 'put'
}>()

const form = useForm({ name: props.initialName ?? '' })

function submit() {
  props.method === 'put' ? form.put(props.action) : form.post(props.action)
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div>
      <label class="block text-sm font-medium">Nome</label>
      <input v-model="form.name" type="text" class="input" />
      <span v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</span>
    </div>
    <button type="submit" :disabled="form.processing" class="btn btn-primary">Salvar</button>
  </form>
</template>
```

#### Position — Form Partial

Dois campos: `name` + `abbreviation`.

#### BadgeType — Form Partial

Campos: `name`, `label`, `description`, `icon`, `scope` (select com 4 opções).

---

## 15. Navegação Admin

A sidebar do painel admin deve incluir uma seção **Catálogo** com links para cada entidade.

### Entrada na sidebar

```ts
// Em resources/js/components/AppSidebar.vue (ou equivalente)

const catalogLinks = [
  { label: 'Modalidades',       href: route('admin.catalog.sport-modes.index') },
  { label: 'Categorias',        href: route('admin.catalog.categories.index') },
  { label: 'Posições',          href: route('admin.catalog.positions.index') },
  { label: 'Formações',         href: route('admin.catalog.formations.index') },
  { label: 'Funções (Comissão)',href: route('admin.catalog.staff-roles.index') },
  { label: 'Tipos de Badge',    href: route('admin.catalog.badge-types.index') },
]
```

> Usar `route()` via Wayfinder, que gera as funções tipadas automaticamente a partir dos controllers.

---

## 16. Testes

### 16.1 Feature — Admin CRUD

Localização: `tests/Feature/Admin/Catalog/`

Um arquivo por entidade. Exemplo completo para `SportModeTest.php`:

```php
namespace Tests\Feature\Admin\Catalog;

use App\Models\SportMode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportModeTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_sport_modes(): void
    {
        $admin = $this->adminUser();
        SportMode::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('admin.catalog.sport-modes.index'));

        $response->assertOk()->assertInertia(fn ($page) =>
            $page->component('admin/catalog/sport-modes/Index')
                 ->has('sportModes.data', 3)
        );
    }

    public function test_admin_can_create_sport_mode(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('admin.catalog.sport-modes.store'), ['name' => 'Beach Soccer']);

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseHas('sport_modes', ['name' => 'Beach Soccer']);
    }

    public function test_admin_can_update_sport_mode(): void
    {
        $admin     = $this->adminUser();
        $sportMode = SportMode::factory()->create(['name' => 'Campo']);

        $response = $this->actingAs($admin)
            ->put(route('admin.catalog.sport-modes.update', $sportMode), ['name' => 'Futebol de Campo']);

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseHas('sport_modes', ['id' => $sportMode->id, 'name' => 'Futebol de Campo']);
    }

    public function test_admin_can_delete_sport_mode(): void
    {
        $admin     = $this->adminUser();
        $sportMode = SportMode::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('admin.catalog.sport-modes.destroy', $sportMode));

        $response->assertRedirect(route('admin.catalog.sport-modes.index'));
        $this->assertDatabaseMissing('sport_modes', ['id' => $sportMode->id]);
    }

    public function test_catalog_requires_authentication(): void
    {
        $response = $this->get(route('admin.catalog.sport-modes.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_sport_mode_name_is_required(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)
            ->post(route('admin.catalog.sport-modes.store'), ['name' => '']);

        $response->assertSessionHasErrors('name');
    }

    public function test_sport_mode_name_must_be_unique(): void
    {
        $admin = $this->adminUser();
        SportMode::factory()->create(['name' => 'Campo']);

        $response = $this->actingAs($admin)
            ->post(route('admin.catalog.sport-modes.store'), ['name' => 'Campo']);

        $response->assertSessionHasErrors('name');
    }
}
```

> Criar factories (`SportModeFactory`, `CategoryFactory`, etc.) para suportar os testes.

### 16.2 Feature — API Catálogo

Localização: `tests/Feature/Api/Catalog/`

```php
namespace Tests\Feature\Api\Catalog;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedUser(): User
    {
        return User::factory()->create();
    }

    public function test_can_list_sport_modes(): void
    {
        $user = $this->authenticatedUser();
        SportMode::factory()->count(4)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/catalog/sport-modes');

        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'data' => [['id', 'name', 'created_at', 'updated_at']],
                     'message',
                 ])
                 ->assertJsonCount(4, 'data');
    }

    public function test_can_list_categories(): void
    {
        $user = $this->authenticatedUser();
        Category::factory()->count(4)->create();

        $this->actingAs($user)->getJson('/api/v1/catalog/categories')
             ->assertOk()
             ->assertJsonCount(4, 'data');
    }

    public function test_can_list_positions(): void
    {
        $user = $this->authenticatedUser();
        Position::factory()->count(5)->create();

        $this->actingAs($user)->getJson('/api/v1/catalog/positions')
             ->assertOk()
             ->assertJsonStructure(['data' => [['id', 'name', 'abbreviation']]]);
    }

    public function test_catalog_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/catalog/sport-modes')->assertUnauthorized();
        $this->getJson('/api/v1/catalog/categories')->assertUnauthorized();
        $this->getJson('/api/v1/catalog/positions')->assertUnauthorized();
        $this->getJson('/api/v1/catalog/formations')->assertUnauthorized();
        $this->getJson('/api/v1/catalog/staff-roles')->assertUnauthorized();
        $this->getJson('/api/v1/catalog/badge-types')->assertUnauthorized();
    }
}
```

### 16.3 Factories necessárias

```
database/factories/
├── SportModeFactory.php
├── CategoryFactory.php
├── PositionFactory.php
├── FormationFactory.php
├── StaffRoleFactory.php
└── BadgeTypeFactory.php
```

Exemplo (`SportModeFactory`):

```php
public function definition(): array
{
    return [
        'name' => fake()->unique()->word(),
    ];
}
```

---

## 17. Checklist de Conclusão

Marcar ao concluir cada item. A fase só está concluída quando todos estiverem ✅.

### Banco

- [x] Migration `sport_modes` criada e validada em teste
- [x] Migration `categories` criada e validada em teste
- [x] Migration `positions` criada e validada em teste
- [x] Migration `formations` criada e validada em teste
- [x] Migration `staff_roles` criada e validada em teste
- [x] Migration `badge_types` criada e validada em teste
- [x] Migration `sport_mode_category` criada e validada em teste
- [x] Migration `sport_mode_formation` criada e validada em teste
- [x] Migration `sport_mode_position` criada e validada em teste

### Seeders

- [x] `SportModeSeeder` com 4 dados
- [x] `CategorySeeder` com 4 dados
- [x] `PositionSeeder` com 17 posições
- [x] `FormationSeeder` com 8 formações
- [x] `StaffRoleSeeder` com 9 funções
- [x] `BadgeTypeSeeder` com 14 tipos
- [x] `SportModeCategorySeeder` associando todas
- [x] `SportModeFormationSeeder` associando por tipo
- [x] `SportModePositionSeeder` associando por tipo

### Backend

- [ ] Enum `BadgeScope` criado
- [ ] Models criados (6)
- [ ] Services criados (6) em `app/Services/Catalog/`
- [ ] Form Requests criados (Store + Update por entidade = 12)
- [ ] API Resources criados (6)
- [ ] API Controllers criados (6) em `App\Http\Controllers\Api\Catalog\`
- [ ] Admin Controllers criados (6) em `App\Http\Controllers\Admin\Catalog\`
- [ ] Rotas API registradas em `routes/api.php`
- [ ] Rotas admin registradas em `routes/admin.php`

### Frontend

- [ ] Types TypeScript criados (6 arquivos em `resources/js/types/catalog/`)
- [ ] Pages `Index.vue`, `Create.vue`, `Edit.vue` para cada entidade (18 arquivos)
- [ ] Partials de form criados (`SportModeForm.vue`, `PositionForm.vue`, `BadgeTypeForm.vue`, etc.)
- [ ] Link de catálogo adicionado na sidebar do admin
- [ ] Wayfinder regenerado (`npm run build` ou `wayfinder:generate`)

### Testes

- [x] Teste de fundação de catálogo criado (`CatalogSetupTest`)
- [ ] Factories criadas (6)
- [ ] Feature tests admin: SportMode, Category, Position, Formation, StaffRole, BadgeType
- [ ] Feature tests API: `CatalogApiTest`
- [ ] Todos os testes passando (`php artisan test`)

---

## 18. Comandos de Referência

```bash
# Criar migrations
php artisan make:migration create_sport_modes_table
php artisan make:migration create_categories_table
php artisan make:migration create_positions_table
php artisan make:migration create_formations_table
php artisan make:migration create_staff_roles_table
php artisan make:migration create_badge_types_table
php artisan make:migration create_sport_mode_category_table
php artisan make:migration create_sport_mode_formation_table
php artisan make:migration create_sport_mode_position_table

# Executar migrations + seeds
php artisan migrate:fresh --seed

# Criar models
php artisan make:model SportMode
php artisan make:model Category
php artisan make:model Position
php artisan make:model Formation
php artisan make:model StaffRole
php artisan make:model BadgeType

# Criar factories
php artisan make:factory SportModeFactory --model=SportMode
# ... repetir para as demais

# Criar seeders
php artisan make:seeder SportModeSeeder
# ... repetir para as demais

# Criar Form Requests
php artisan make:request Catalog/StoreSportModeRequest
php artisan make:request Catalog/UpdateSportModeRequest
# ... repetir para as demais

# Criar Resources
php artisan make:resource Catalog/SportModeResource
# ... repetir para as demais

# Criar enum
php artisan make:enum Enums/BadgeScope

# Rodar testes
php artisan test
php artisan test --filter=SportModeTest
php artisan test --filter=CatalogApiTest

# Regenerar Wayfinder após adicionar rotas
npm run build
```
