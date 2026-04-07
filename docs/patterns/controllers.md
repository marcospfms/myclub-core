# Controllers

## Objetivo

Padronizar controllers HTTP do projeto, respeitando a separação entre **API** e **admin/web**.

---

## Tipos de controller

### 1. API Controllers

- namespace: `App\Http\Controllers\Api\...`
- retornam apenas JSON
- devem estender `App\Http\Controllers\Api\BaseController`
- usam `FormRequest` para validação
- usam `Resource` para serialização
- delegam regra de negócio para `Services`

### 2. Web/Admin Controllers

- namespace: `App\Http\Controllers\Admin\...`, `App\Http\Controllers\Settings\...` ou equivalente
- retornam `Inertia\Response`, `RedirectResponse` ou respostas web equivalentes
- **não** usam envelope `success/data/message`
- delegam regra de negócio para os mesmos `Services` usados pela API

---

## Responsabilidade do controller

- receber request
- autorizar acesso
- delegar validação ao `FormRequest`
- chamar o service do contexto
- retornar `Resource` + `BaseController` na API
- retornar `Inertia` ou redirect na interface administrativa

---

## O que não colocar no controller

- regra de negócio extensa
- transações complexas
- queries grandes e reutilizáveis espalhadas inline
- serialização manual repetitiva
- integração direta com múltiplas dependências sem encapsulamento

---

## Estrutura recomendada

### API

- `index()` para listagem
- `store()` para criação
- `show()` para detalhe
- `update()` para atualização
- `destroy()` para remoção

### Web/Admin

- `index()` para listagem administrativa
- `create()` e `edit()` para render de páginas/formulários
- `store()`, `update()`, `destroy()` para ações com redirect ou refresh

---

## Exemplo de API controller

```php
class TeamController extends BaseController
{
    public function __construct(
        private TeamService $teamService,
    ) {
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->create($request->validated(), $request->user());

        return $this->sendResponse(
            new TeamResource($team),
            'Team created successfully.',
            201
        );
    }
}
```

## Exemplo de web/admin controller

```php
class TeamController extends Controller
{
    public function __construct(
        private TeamService $teamService,
    ) {
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->teamService->create($request->validated(), $request->user());

        return to_route('admin.teams.index');
    }
}
```

---

## Checklist de revisão

- o tipo de controller está correto para a superfície
- validação está em `FormRequest`
- regra de negócio está em `Service`
- controller está fino
- API usa `BaseController`
- API usa `Resource`
- web/admin não usa envelope de API
