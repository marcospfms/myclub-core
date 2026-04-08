# Roadmap Geral — MyClub

> Documento de visão de alto nível. Cada fase terá seu próprio roadmap detalhado criado antes da implementação.
> Escopo mínimo por fase — sem detalhe de implementação aqui.

> **Gaps e inconsistências:** registrados e rastreados em `docs/database/gaps.md`.

---

## Roadmap por fases

### Fase 0 — Fundação _(atual — parcialmente concluída)_

O que implementar:

- Estrutura base do projeto (Laravel, Sanctum, Fortify, Inertia + Vue) ✓
- Tabelas de catálogo: `sport_modes`, `categories`, `positions`, `formations`, `staff_roles`, `badge_types`
- Seeders com dados de referência
- Painel admin mínimo (criação/edição de catálogos)

---

### Fase 1 — Identidade, Times e Elenco

O que implementar:

- `players`, `staff_members` — extensões de usuário
- `teams`, `team_sport_modes`, `team_staff` — estrutura de times
- `team_invitations` — convites de elenco _(resolve G2)_
- `player_memberships` — vínculos aceitos
- API: CRUD de times, gestão de elenco, fluxo de convite/aceite

---

### Fase 2 — Amistosos

O que implementar:

- `friendly_matches`, `performance_highlights`
- Ciclo de vida completo: convite → confirmação → resultado bilateral → completed
- Campo `invite_expires_at` _(resolve G7)_
- Expiração automática de convites (job/scheduler)
- Notificações in-app (Laravel Notifications)
- API: amistosos completo

---

### Fase 3 — Campeonatos (formato `league`)

O que implementar:

- `championships`, `championship_sport_modes`, `championship_teams`
- `championship_phases`, `championship_groups`, `championship_group_entries`
- `championship_rounds`, `championship_matches`, `championship_match_highlights`
- `championship_team_players` — seleção de jogadores por time
- `championship_awards` — ao encerrar
- `player_badges` — badges por campeonato
- Adicionar `category_id` em `championships` _(resolve G6)_
- Adicionar `location` em `championship_matches` _(resolve G10)_
- Lifecycle completo: draft → enrollment → active → finished → archived
- API: campeonatos formato league

---

### Fase 4 — Planos e Feature Gating

O que implementar:

- Definição do modelo de plano (campo ou tabela) _(resolve G1)_
- Middleware de verificação de plano
- Limites por plano no backend (1 time Free, campeonato `league` Free, etc.)
- AdSense toggle (exibido apenas no tier Free)

---

### Fase 5 — Player Pro e Descoberta de Jogadores

O que implementar:

- `player_sport_preferences` — preferências de modalidade para busca
- URL amigável (`@slug` em `users`)
- Cartão digital exportável
- Ranking com destaque Player Pro
- API: busca de jogadores por posição/modalidade/localização
- Definir acesso à busca (resolve decisão aberta de user-personas.md §7)

---

### Fase 6 — Rankings e Cache

O que implementar:

- `team_stats_cache` — snapshot para listagens
- Cálculo de ranking (dinâmico + cache write-through)
- Rankings públicos de times na plataforma
- `teams.is_active` _(resolve G9)_

---

### Fase 7 — Campeonatos avançados (Club: knockout, cup)

O que implementar:

- Suporte a fases `knockout` com chaveamento automático
- Suporte a formato `cup` (grupos + mata-mata)
- Campeonatos multi-fase
- Badges coletivos de time _(resolve G3 — decisão de modelagem definida aqui)_

---

### Fase 8 — Liga: campeonatos públicos e API de leitura

O que implementar:

- `championships.is_public` — visibilidade externa
- Busca pública de campeonatos
- API de leitura pública (rate-limited)
- Push notifications mobile: `device_tokens` _(resolve G11)_

---

### Fase 9 — Federação

O que implementar:

- White-label (customização por organização)
- Gráficos analíticos de desempenho por temporada
- Comparativo percentual por posição/modalidade
- Integrações externas / webhooks
- SLA e suporte dedicado

---

## Decisões em aberto

> Nenhuma decisão pendente. Todos os bloqueadores foram resolvidos. Ver histórico em `docs/database/gaps.md`.
