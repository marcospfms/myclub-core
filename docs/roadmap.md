# Roadmap Geral — MyClub

> Documento de visão de alto nível. Cada fase terá seu próprio roadmap detalhado criado antes da implementação.
> Escopo mínimo por fase — sem detalhe de implementação aqui.

---

## Gaps e inconsistências identificadas na revisão

### G1 — Ausência de rastreamento de plano/assinatura do usuário ⚠️ crítico

Não existe tabela ou campo que registre qual plano um usuário está ativo (Free, Player Pro, Club, Liga, Federação). O feature gating está documentado em `docs/product/feature-gating.md` mas não tem representação no schema. Sem isso, nenhuma verificação de plano é possível no backend.

**Impacto:** bloqueador para qualquer feature paga. Deve ser definido antes da Fase 2.

**Decisão necessária:** plano no nível do usuário (`users.plan` + `users.plan_expires_at`) ou tabela dedicada de assinaturas (`user_subscriptions` com histórico). A tabela dedicada é o caminho correto se houver pagamento processado na plataforma futuramente; o campo simples é suficiente enquanto o cadastro de planos for manual/operacional.

---

### G2 — Sem tabela de convites de elenco ⚠️ crítico

`docs/product/player-membership-rules.md` define que adicionar um jogador ao elenco requer aprovação do atleta via convite. Porém, o schema não tem onde rastrear o convite antes da aceitação.

O fluxo documentado é: dono envia convite → jogador aceita → `player_memberships` é criado. Mas não há tabela `team_invitations` (ou campo `status` em `player_memberships` para estado `pending`) que suporte o estado intermediário.

**Impacto:** sem essa tabela, a regra de aprovação não pode ser implementada.

---

### G3 — Sem `team_badges` para badges coletivos ⚠️ importante

`docs/product/championship-lifecycle.md` menciona que badges de conquista coletiva (ex: `unbeaten_champion`) são atribuídos ao **perfil do time**, mas só existe `player_badges`. Não há tabela `team_badges` nem campo `team_id` em `player_badges`.

**Impacto:** badges coletivos não podem ser associados a um time. Ou define-se uma tabela `team_badges` separada, ou repensa-se se esses badges vão apenas para os jogadores que participaram.

**Decisão necessária:** badges coletivos ficam em `player_badges` (distribuídos para todos os jogadores campeões) ou em uma tabela `team_badges` no perfil do time.

---

### G4 — `performance_highlights` sem PK surrogate

Classificada como entidade associativa na seção 9 (deve ter Model Eloquent próprio), mas os campos documentados não incluem `id`. Pivôs puros podem omitir o PK; entidades associativas não.

**Correção:** adicionar `id` bigint PK à tabela e à documentação.

---

### G5 — `championship_group_entries` sem PK surrogate

Tem campo próprio (`final_position`) que a torna uma entidade associativa de fato, mas está listada como "Pivot puro" na seção 9 e não tem `id` documentado.

**Correção:** adicionar `id` bigint PK e mover para a coluna "Entidade associativa" na seção 9.

---

### G6 — `championships` sem `category_id`

Um campeonato Sub-17 de Futsal não tem como registrar a categoria. O schema tem a tabela `categories` (Livre, Sub-15, Sub-17, Sub-20) mas nenhuma FK aponta para ela a partir de `championships`. O mesmo vale para `team_sport_modes` — não fica registrado em qual categoria aquele time compete.

**Impacto:** não é possível criar campeonatos por faixa etária nem filtrar times por categoria.

**Correção sugerida:** adicionar `category_id` (FK → `categories`, nullable) em `championships`. Para `team_sport_modes`, avaliar se a categoria é por time/modalidade ou apenas por campeonato.

---

### G7 — `friendly_matches` sem `expires_at` para o convite

`docs/product/friendly-match-flow.md` define que o convite expira automaticamente em 2 dias. Não há campo que registre quando o convite expira, nem quando foi enviado. O sistema precisaria inferir isso a partir de `created_at`, o que é frágil e pouco explícito.

**Correção:** adicionar `invite_expires_at` (timestamp, nullable) em `friendly_matches`.

---

### G8 — Badges `seasonal` sem conceito de temporada

`badge_types.scope = seasonal` existe no catálogo, mas não há tabela `seasons` nem campo `year`/`season_id` para delimitar o que é uma temporada. O critério de "Artilheiro da Temporada" (badge `top_scorer_season`) não pode ser calculado sem definir o intervalo de tempo de uma temporada.

**Impacto:** badges seasonais não podem ser computados nem atribuídos corretamente.

**Decisão necessária:** temporada é baseada em ano-calendário (campo `year` INT em `player_badges`) ou em uma tabela `seasons` com intervalo de datas?

---

### G9 — `teams` sem `is_active`

Não há como desativar um time sem removê-lo do banco. Times abandonados (sem atividade) continuariam aparecendo em buscas e rankings.

**Correção:** adicionar `is_active` boolean (default `true`) em `teams`.

---

### G10 — `championship_matches` sem `location` individual

O campeonato tem `location` geral, mas partidas individuais não registram local (venue). Em campeonatos com múltiplas fases acontecendo em locais diferentes, isso é um gap funcional.

**Correção:** adicionar `location` (varchar(255), nullable) em `championship_matches`.

---

### G11 — Sem infraestrutura de notificações push

`docs/product/friendly-match-flow.md` define notificações in-app + push mobile para convites de amistoso. Não há tabela `device_tokens` para registrar dispositivos mobile. O sistema de notificações in-app pode ser implementado com as notifications do Laravel, mas push mobile requer persistência dos tokens.

**Para Fase 1:** notificações in-app via Laravel Notifications são suficientes.
**Para Fase 3+:** adicionar tabela `device_tokens` (user_id, token, platform, created_at).

---

### G12 — Diagramas secionais (seções 1, 3, 4) desatualizados

O diagrama mermaid da **seção 8** (schema completo) está atualizado. Mas os diagramas menores das seções 1, 3 e 4 ainda refletem o estado anterior — sem os novos campos de `players`, `player_memberships`, `player_sport_preferences`, `championship_team_players`, `advances_count`, etc.

**Impacto:** documentação inconsistente. Não é bloqueador técnico, mas gera confusão ao ler cada seção individualmente.

---

### G13 — `player_badges` sem campo para temporada (badges seasonal)

Relacionado ao G8: mesmo que se decida usar apenas `year` INT (sem tabela `seasons`), o campo não está documentado em `player_badges`. A unicidade para badges de carreira/seasonais também é definida apenas como "a aplicação controla", sem constraint documentada.

---

## Roadmap por fases

### Fase 0 — Fundação *(atual — parcialmente concluída)*

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
- `team_invitations` — convites de elenco *(resolve G2)*
- `player_memberships` — vínculos aceitos
- API: CRUD de times, gestão de elenco, fluxo de convite/aceite

---

### Fase 2 — Amistosos

O que implementar:
- `friendly_matches`, `performance_highlights`
- Ciclo de vida completo: convite → confirmação → resultado bilateral → completed
- Campo `invite_expires_at` *(resolve G7)*
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
- Adicionar `category_id` em `championships` *(resolve G6)*
- Adicionar `location` em `championship_matches` *(resolve G10)*
- Lifecycle completo: draft → enrollment → active → finished → archived
- API: campeonatos formato league

---

### Fase 4 — Planos e Feature Gating

O que implementar:
- Definição do modelo de plano (campo ou tabela) *(resolve G1)*
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
- `teams.is_active` *(resolve G9)*

---

### Fase 7 — Campeonatos avançados (Club: knockout, cup)

O que implementar:
- Suporte a fases `knockout` com chaveamento automático
- Suporte a formato `cup` (grupos + mata-mata)
- Campeonatos multi-fase
- Badges coletivos de time *(resolve G3 — decisão de modelagem definida aqui)*

---

### Fase 8 — Liga: campeonatos públicos e API de leitura

O que implementar:
- `championships.is_public` — visibilidade externa
- Busca pública de campeonatos
- API de leitura pública (rate-limited)
- Push notifications mobile: `device_tokens` *(resolve G11)*

---

### Fase 9 — Federação

O que implementar:
- White-label (customização por organização)
- Gráficos analíticos de desempenho por temporada
- Comparativo percentual por posição/modalidade
- Integrações externas / webhooks
- SLA e suporte dedicado

---

## Decisões em aberto (bloqueiam implementação futura)

| Decisão | Bloqueia | Referência |
| --- | --- | --- |
| Plano: campo em `users` ou tabela `user_subscriptions`? | Fase 4 inteira | G1 |
| Badges coletivos: `team_badges` separado ou distribuídos para jogadores? | Fase 7 badges | G3 |
| Temporada: ano-calendário (campo `year`) ou tabela `seasons`? | Fase 3+ badges seasonal | G8 |
| Busca de jogadores: qualquer logado ou apenas Club+? | Fase 5 discovery | user-personas.md §7 |
| Preço final do Player Pro (est. R$ 3,90–9,90/mês) | Fase 4 planos | feature-gating.md |
| Limite de campeonatos ativos simultâneos por plano | Fase 4 gating | feature-gating.md |
| "Histórico básico" vs "histórico completo" no Free | Fase 4 gating | feature-gating.md |
| `category_id` em `team_sport_modes` ou apenas em `championships`? | Fase 1/3 | G6 |
