# Padrões de Criação de Endpoints

## Objetivo

Padronizar a criação de endpoints no `myclub-core`, respeitando:

- API pública do produto em `routes/api.php`
- interface administrativa em rotas web/Inertia
- compartilhamento de domínio via `Services`

---

## Arquitetura esperada

### Camadas

1. **Migration**
   - define schema novo em inglês
   - não copiar defeitos do banco legado

2. **Model**
   - representa entidade do domínio
   - define relacionamentos, casts e proteção de atribuição em massa

3. **Form Request**
   - valida entrada
   - autorização específica quando aplicável

4. **Service**
   - concentra regra de negócio
   - pode executar transações, regras compostas, sincronizações e integrações

5. **Resource**
   - serializa resposta da API
   - garante contrato em `snake_case`

6. **Controller**
   - orquestra request, service e resposta

7. **Route**
   - registra endpoint na superfície correta

---

## Fluxo padrão para novo endpoint de API

### 1. Banco e model

- criar migration
- criar model
- definir relacionamentos e casts
- garantir nomes em inglês e sem heranças desnecessárias do schema legado

### 2. Validação

- criar `StoreXRequest`
- criar `UpdateXRequest` quando necessário

### 3. Service

- criar ou evoluir um service do contexto
- colocar nele toda a regra de negócio relevante

### 4. Resource

- criar `XResource`
- usar `snake_case` nas chaves

### 5. Controller

- criar controller em `App\Http\Controllers\Api\...`
- estender `BaseController`
- chamar o service
- devolver `Resource` via `sendResponse()`

### 6. Rotas

- registrar em `routes/api.php`
- usar prefixo versionado, ex.: `/api/v1/...`
- aplicar `auth:sanctum` quando a rota for protegida

### 7. Testes

- testar sucesso
- testar validação
- testar autenticação/autorização
- testar contrato JSON

---

## Fluxo padrão para endpoint/admin screen

- criar controller web/admin
- reutilizar o mesmo service do domínio
- retornar `Inertia::render()` ou redirect
- não usar `BaseController`
- não usar envelope `success/data/message`

---

## Checklist

- a superfície correta foi escolhida
- nomes estão em inglês
- validação está fora do controller
- regra de negócio está em service
- API usa resource
- API responde em `snake_case`
- endpoint web/admin não replica lógica da API
- testes cobrem comportamento real
