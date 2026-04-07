# Padrões Frontend - Vue 3 + Inertia

## Papel do frontend neste projeto

O frontend Inertia + Vue do `myclub-core` é **administrativo**.

Ele não é o frontend principal do produto para cliente, jogador ou dono de time. Essas experiências serão servidas por outras stacks consumindo a API.

---

## Objetivo do admin

- operação interna
- cadastros administrativos
- gestão de entidades do domínio
- suporte operacional e acompanhamento

---

## Estado atual vs estrutura-alvo

### Estado atual

O starter recém-criado tem estrutura básica do Laravel + Inertia:

- páginas genéricas
- auth pages
- settings pages
- componentes ainda organizados de forma mais simples

### Estrutura-alvo do projeto

Novos módulos administrativos devem seguir organização por domínio:

```text
resources/js/
├── components/
│   └── [domain]/
├── composables/
├── layouts/
├── pages/
│   └── admin/
│       └── [domain]/
├── types/
│   └── [domain]/
└── lib/
```

Exemplo:

```text
resources/js/pages/admin/teams/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Partials/
```

---

## Regras de implementação

- usar Vue 3 com `<script setup>`
- usar TypeScript em páginas, componentes e composables
- organizar novos módulos por domínio
- não criar telas administrativas dependentes de chamadas HTTP para a própria API interna
- controllers web/admin devem injetar os dados diretamente no Inertia, reutilizando os mesmos `Services`

---

## Componentes

- componentes reutilizáveis ficam em `components/`
- componentes específicos de domínio ficam em `components/[domain]/`
- evitar componente “genérico demais” sem uso real
- extrair apenas quando houver reutilização ou ganho claro de legibilidade

---

## Páginas

- páginas administrativas devem viver em `pages/admin/[domain]/...`
- páginas do starter existentes podem permanecer enquanto o projeto ainda estiver vazio
- novos módulos do domínio devem seguir a estrutura-alvo desde a primeira entrega

---

## Types e composables

- `types/[domain]` para tipos específicos do domínio
- `composables/` para lógica reutilizável de frontend
- não mover lógica de negócio do backend para o frontend

---

## Boas práticas

- evitar páginas grandes e acopladas
- manter labels, mensagens e estados de loading claros
- usar nomenclatura técnica em inglês no código
- manter textos/documentação do time em português quando conveniente
