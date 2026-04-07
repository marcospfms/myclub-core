# MyClub Core

## Visão geral

O **MyClub Core** é o núcleo backend do ecossistema MyClub.

A proposta do produto é centralizar a operação de futebol amador e semiprofissional em uma plataforma única, permitindo cadastrar e organizar:

- usuários
- jogadores
- técnicos
- times
- modalidades
- categorias
- posições
- campeonatos
- amistosos
- desempenho individual

Este repositório **não** é o frontend principal do produto final. Ele será o coração do sistema, responsável pela regra de negócio, persistência, autenticação e exposição da API.

---

## Ideia do produto

O MyClub nasce para resolver problemas comuns de organização esportiva, especialmente em cenários onde a gestão ainda acontece de forma manual, descentralizada ou improvisada.

### Problemas que o projeto busca resolver

- dificuldade para organizar elencos e vínculos de jogadores com times
- falta de padronização na gestão de amistosos e campeonatos
- ausência de histórico de desempenho dos atletas
- pouca rastreabilidade sobre quem participa de qual modalidade, categoria e posição
- dependência de grupos, planilhas e controles informais

### O que a plataforma pretende oferecer

- cadastro estruturado de usuários e perfis esportivos
- gestão de times e seus responsáveis
- vínculo de jogadores por time e modalidade
- criação e administração de campeonatos
- organização de amistosos entre equipes
- acompanhamento de estatísticas e destaques
- base sólida para integrações com apps e outras interfaces

---

## Papel deste repositório

Este projeto foi criado como o **core backend** do sistema.

Ele terá duas superfícies distintas:

### 1. API pública do produto

A API será a superfície principal do sistema.

Ela será consumida por outras stacks e interfaces, incluindo experiências voltadas para:

- cliente final
- jogador
- dono de time
- aplicações mobile
- possíveis integrações futuras

### 2. Interface administrativa

O projeto também possui uma interface web com Laravel + Inertia + Vue.

Essa interface **não é o produto final para o usuário externo**. Ela existe para:

- operação interna
- administração
- manutenção de cadastros
- apoio à gestão do sistema

---

## Arquitetura definida

### Separação entre API e web/admin

O projeto adota separação explícita entre API e interface administrativa:

- `routes/api.php` para a API pública
- `routes/web.php` e `routes/admin.php` para a interface administrativa
- controllers de API em `App\Http\Controllers\Api`
- controllers web/admin em namespaces próprios

As duas superfícies compartilham o mesmo domínio, mas não compartilham o mesmo transporte HTTP.

O admin **não deve consumir a própria API por HTTP interno**. Em vez disso, API e admin devem reutilizar os mesmos serviços de domínio.

### Services como padrão único

Toda lógica de negócio fica em **Services**.

Neste projeto:

- não usamos `Actions` como padrão
- não usamos `Queries` como padrão
- controllers devem ser finos
- services concentram regras de negócio, transações, consultas compostas e orquestrações

### Contrato da API

A API segue os seguintes padrões:

- respostas em JSON
- chaves em `snake_case`
- serialização obrigatória com `Resources`
- envelope padrão com:
  - `success`
  - `data`
  - `message`

Para isso, controllers de API devem estender `App\Http\Controllers\Api\BaseController`.

### Banco de dados

O projeto usa como referência de domínio o arquivo legado:

- `Banco de dados/dump_myclub.sql`

Esse dump será tratado como **referência funcional**, não como contrato estrutural definitivo.

A nova base deve ser reconstruída com:

- migrations Laravel
- nomenclatura em inglês
- modelagem corrigida
- constraints explícitas
- estrutura mais adequada ao Eloquent e à evolução do produto

---

## Convenções do projeto

### Idioma

- **código em inglês**
- **documentação em português**

Isso vale para:

- classes
- migrations
- models
- services
- tabelas
- nomes de campos
- nomes de rotas

Enquanto:

- README
- documentação de padrões
- descrições de arquitetura
- planos

podem permanecer em português.

### Autenticação

- **API**: Sanctum tokens
- **admin web**: Fortify + sessão

---

## Stack atual

- Laravel 13
- PHP 8.4+
- Inertia.js
- Vue 3
- TypeScript
- Tailwind CSS
- Laravel Fortify
- Laravel Sanctum
- Laravel Boost

---

## Estado atual do projeto

Neste momento, o `myclub-core` está em fase de fundação arquitetural.

Já existe:

- estrutura inicial Laravel
- starter administrativo com Inertia + Vue
- base de rotas separando API e web
- `BaseController` para API
- documentação de padrões alinhada ao projeto

Ainda será construído:

- modelagem completa do domínio em migrations
- models e relacionamentos do novo schema
- services de domínio
- recursos e endpoints reais da API
- módulos administrativos por domínio

---

## Estrutura esperada de evolução

### Backend

- domínio em inglês
- services organizados por contexto
- API versionada
- resources padronizados
- migrations como fonte oficial do schema

### Frontend administrativo

- organização por domínio
- páginas administrativas em `resources/js/pages/admin/[domain]`
- componentes e tipos específicos por domínio

---

## Direção de domínio esperada

Com base no banco legado, este projeto deve evoluir para cobrir conceitos como:

- `User`
- `Player`
- `Coach`
- `Team`
- `SportMode`
- `Category`
- `Position`
- `Championship`
- `FriendlyMatch`
- `PlayerMembership`
- `PerformanceHighlight`

Os nomes finais e o desenho completo serão validados durante a revisão completa do schema legado.

---

## Objetivo de longo prazo

O objetivo é transformar o legado do MyClub em uma base moderna, organizada e sustentável, capaz de:

- servir múltiplas interfaces
- suportar crescimento do domínio
- facilitar manutenção
- permitir evolução segura da regra de negócio
- reduzir improviso estrutural herdado do sistema anterior

---

## Documentação complementar

Os padrões internos ficam em:

- `docs/patterns/README.md`
- `docs/patterns/controllers.md`
- `docs/patterns/endpoint-creation-standards.md`
- `docs/patterns/services.md`
- `docs/patterns/models.md`
- `docs/patterns/resources.md`
- `docs/patterns/tests.md`
- `docs/patterns/frontend-vue-inertia.md`
- `docs/patterns/page-structure-standard.md`

---

## Observação final

O MyClub Core deve ser entendido como a base oficial do novo sistema, e não como uma simples migração literal do projeto antigo.

A intenção é preservar o domínio e a ideia do produto, mas reconstruir a implementação com padrões modernos e uma arquitetura mais clara.
