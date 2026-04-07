# Padrões e Convenções do MyClub Core

## Objetivo

Garantir consistência arquitetural entre as duas superfícies do projeto:

- **API pública do produto**: JSON API consumida por outras stacks
- **Interface administrativa**: Laravel + Inertia + Vue para operação interna

As duas superfícies compartilham o mesmo domínio e os mesmos **Services**, mas não compartilham o mesmo transporte HTTP.

---

## Princípios-base

### 1. Separação de superfícies

- rotas de API ficam em `routes/api.php`
- rotas administrativas/web ficam em `routes/web.php`, `routes/admin.php` e arquivos auxiliares
- controllers de API ficam em `App\Http\Controllers\Api`
- controllers de admin/web ficam em `App\Http\Controllers\Admin` ou `App\Http\Controllers\Settings`

### 2. Contrato da API

- toda resposta de API deve usar **Resource**
- toda resposta de API deve usar `snake_case`
- todo controller de API deve estender `App\Http\Controllers\Api\BaseController`
- o envelope padrão da API é:
  - `success`
  - `data`
  - `message`

### 3. Lógica de negócio

- a lógica de negócio fica em **Services**
- não usar `Actions` nem `Queries` como padrão do projeto
- controllers devem ser finos: request, autorização, chamada de service e response/resource

### 4. Banco e domínio

- código, classes, tabelas, migrations e identificadores técnicos em **inglês**
- documentação, padrões e planos podem ficar em **português**
- o arquivo legado `Banco de dados/dump_myclub.sql` é referência de domínio, não contrato estrutural
- a base nova deve ser reconstruída com migrations Laravel

### 5. Admin por domínio

- a interface administrativa deve crescer com organização por domínio
- a estrutura atual do starter é apenas a base inicial
- novas páginas, componentes e types devem ser organizados pensando no domínio desde já

---

## Documentos disponíveis

### Backend

1. [controllers.md](./controllers.md)
2. [models.md](./models.md)
3. [resources.md](./resources.md)
4. [services.md](./services.md)
5. [endpoint-creation-standards.md](./endpoint-creation-standards.md)
6. [tests.md](./tests.md)

### Frontend administrativo

7. [frontend-vue-inertia.md](./frontend-vue-inertia.md)
8. [page-structure-standard.md](./page-structure-standard.md)

---

## Regras de revisão

### Backend/API

- controller de API estende `BaseController`
- request de validação separado do controller
- response serializado com Resource
- chaves da API em `snake_case`
- regra de negócio fora do controller
- sem duplicação de lógica entre API e admin

### Web/Admin

- controller web retorna Inertia, redirect ou view, nunca envelope de API
- páginas e componentes seguem organização por domínio
- admin não consome a própria API via HTTP interno

### Banco

- migrations em inglês
- relacionamentos e constraints explícitos
- evitar carregar defeitos do schema legado

---

## Observações importantes

- autenticação da API será feita com **Sanctum tokens**
- autenticação da interface administrativa permanece via **Fortify/sessão**
- ao implementar novas features, pensar primeiro no domínio e depois nas duas superfícies
