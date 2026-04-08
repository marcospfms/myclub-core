# Gaps e Inconsistências do Schema — MyClub

> Registro centralizado de gaps identificados entre a documentação de produto e o schema do banco de dados.
> Status atualizado conforme resolução em `schema.md`.

---

## Legenda de status

| Status        | Significado                                              |
| ------------- | -------------------------------------------------------- |
| ✅ Resolvido  | Campo/tabela adicionado ao schema                        |
| ⏳ Diferido   | Decisão tomada; implementação planejada para fase futura |
| 🔍 Em análise | Decisão pendente                                         |

---

## Gaps originais (G1–G13)

### G1 — Ausência de rastreamento de plano/assinatura do usuário ✅ Resolvido

**Impacto:** bloqueador para qualquer feature paga.

**Resolução:** campos `users.plan` (enum: `free/player_pro/club/liga/federation`, default `free`) e `users.plan_expires_at` (timestamp nullable) adicionados à tabela `users`.

**Decisão:** abordagem campo simples para a Fase 4. Quando payment processing for integrado, evoluir para tabela `user_subscriptions` com histórico. O campo `users.plan` permanece como cache de leitura rápida para Gates/Policies.

---

### G2 — Sem tabela de convites de elenco ✅ Resolvido

**Impacto:** a regra de aprovação de jogadores (convite → aceite → membership) não pode ser implementada sem estado intermediário.

**Resolução:** tabela `team_invitations` adicionada à seção 3 com campos: `id`, `team_sport_mode_id`, `invited_user_id`, `invited_by`, `position_id`, `status (pending/accepted/rejected/expired)`, `expires_at`, `message`.

> O convite usa `invited_user_id` (FK → `users`) e não `player_id`, pois o usuário pode ainda não ter perfil de jogador ao receber o convite.

---

### G3 — Sem `team_badges` para badges coletivos ✅ Resolvido

**Impacto:** badges de conquista coletiva não podiam ser associados ao perfil do time.

**Resolução:** tabela `team_badges` adicionada à seção 6 com campos: `id`, `team_id`, `badge_type_id`, `championship_id`, `awarded_at`, `notes`.

**Decisão:** atribuição dual — ao encerrar o campeonato, o sistema registra badges coletivos simultaneamente em `player_badges` (para cada jogador em `championship_team_players`) e em `team_badges` (para o perfil do time).

---

### G4 — `performance_highlights` sem PK surrogate ✅ Resolvido

**Impacto:** entidade associativa sem `id` impede Model Eloquent próprio.

**Resolução:** campo `id` (bigint PK surrogate) adicionado como primeiro campo de `performance_highlights`.

---

### G5 — `championship_group_entries` sem PK surrogate ✅ Resolvido

**Impacto:** classificada como "Pivot puro" na seção 9, mas possui campo próprio (`final_position`) que a torna entidade associativa.

**Resolução:** campo `id` (bigint PK surrogate) adicionado. Tabela reclassificada de "Pivot puro" para "Entidade associativa" na seção 9.

---

### G6 — `championships` sem `category_id` ✅ Resolvido

**Impacto:** impossível criar campeonatos por faixa etária (Sub-17, Sub-20).

**Resolução:** campo `category_id` (bigint FK nullable → `categories`) adicionado à tabela `championships`.

**Decisão:** `category_id` fica apenas em `championships` (nullable). Não em `team_sport_modes` — um time pode competir em categorias diferentes dependendo do campeonato.

---

### G7 — `friendly_matches` sem `invite_expires_at` ✅ Resolvido

**Impacto:** expiração automática de convite em 2 dias não tinha campo de suporte; inferir de `created_at` é frágil.

**Resolução:** campo `invite_expires_at` (timestamp nullable) adicionado à tabela `friendly_matches`, logo após `confirmation`.

---

### G8 — Badges `seasonal` sem conceito de temporada ✅ Resolvido

Ver G13.

---

### G9 — `teams` sem `is_active` ✅ Resolvido

**Impacto:** times abandonados continuariam aparecendo em buscas e rankings.

**Resolução:** campo `is_active` (boolean, default `true`) adicionado à tabela `teams`.

---

### G10 — `championship_matches` sem `location` individual ✅ Já resolvido anteriormente

**Resolução:** campo `location` (varchar(255), nullable) já estava presente na tabela `championship_matches` antes desta revisão.

---

### G11 — Sem infraestrutura de notificações push ⏳ Diferido

**Resolução planejada:**

- **Fase 1–2:** notificações in-app via `Laravel Notifications` canal `database` (tabela `notifications` criada pelo Laravel automaticamente).
- **Fase 8+:** adicionar tabela `device_tokens` (`id`, `user_id FK`, `token`, `platform`, `created_at`) para push mobile.

> Documentado na seção 9 (Observações de modelagem) de `schema.md`.

---

### G12 — Diagramas secionais desatualizados ✅ Resolvido

**Resolução:** todos os diagramas das seções 1, 3, 4, 5, 6 e 8 atualizados para refletir os campos e tabelas correntes.

| Seção               | Atualizações                                                                                                                                                                                                                                              |
| ------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1 (Identidade)      | `users`: `last_login_at`, `plan`, `plan_expires_at`, `slug`; `players`: campos de descoberta; nova entidade `player_sport_preferences`                                                                                                                    |
| 3 (Times)           | `teams`: `is_active`; `player_memberships`: `left_at`; nova entidade `team_invitations`                                                                                                                                                                   |
| 4 (Campeonatos)     | `championships`: `status`, `created_by`, `max_players`, `category_id`; `championship_phases`: `advances_count`; `championship_group_entries`: `id PK`; `championship_matches`: `home_penalties`, `away_penalties`; nova entidade `categories` com relação |
| 5 (Partidas)        | `friendly_matches`: `invite_expires_at`, `is_public`, `result_status`, `result_registered_by`; `performance_highlights`: `id PK`                                                                                                                          |
| 6 (Badges)          | `player_badges`: `year`; nova entidade `team_badges`                                                                                                                                                                                                      |
| 8 (Schema completo) | Todas as tabelas e relações acima                                                                                                                                                                                                                         |

---

### G13 — `player_badges` sem campo para temporada ✅ Resolvido

**Impacto:** badges de escopo `seasonal` não podiam ser calculados nem atribuídos corretamente.

**Resolução:** campo `year` (int nullable) adicionado à tabela `player_badges`.

**Decisão:** temporada = ano-calendário (campo `year` INT). Sem tabela `seasons` — desnecessário para o escopo atual.

**Unicidade atualizada:**

- `(player_id, badge_type_id, championship_id)` quando `scope = championship`
- `(player_id, badge_type_id, year)` quando `scope = seasonal`
- `(player_id, badge_type_id)` quando `scope = career` (único por carreira)
- `friendly`: a aplicação controla via regra de negócio

---

## Gaps novos — identificados na revisão de abril/2026

### G14 — `friendly_matches.confirmation` sem status `expired` ✅ Resolvido

**Origem:** `docs/product/friendly-match-flow.md` — o convite expira automaticamente em 2 dias, mas o enum `confirmation` original só continha `pending/confirmed/rejected`. Expirar definindo `confirmation = rejected` seria semanticamente incorreto (o time não recusou — o prazo passou).

**Resolução:** valor `expired` adicionado ao enum `confirmation` de `friendly_matches`.

---

### G15 — `championship_matches` sem `home_penalties`/`away_penalties` ✅ Resolvido

**Origem:** `docs/product/championship-lifecycle.md` — "empate na fase eliminatória define vencedor em pênaltis". Sem campos dedicados, seria impossível registrar o placar dos pênaltis separadamente do placar regulamentar.

**Resolução:** campos `home_penalties` (int nullable) e `away_penalties` (int nullable) adicionados à tabela `championship_matches`.

---

### G16 — `users` sem campo `slug` para URL amigável ✅ Resolvido

**Origem:** `docs/product/feature-gating.md` — feature "URL amigável `myclub.com.br/@nome`" para plano Player Pro+. O campo `slug` não existia no schema.

**Resolução:** campo `slug` (varchar(30), nullable, único) adicionado à tabela `users`. Exibido publicamente apenas para usuários com `plan = player_pro` ou superior. Implementação efetiva na **Fase 5** do produto.

---

## Decisões fechadas nesta revisão

| Gap    | Decisão tomada                                                                                                                                         |
| ------ | ------------------------------------------------------------------------------------------------------------------------------------------------------ |
| G1     | `users.plan` enum + `users.plan_expires_at`; evolui para `user_subscriptions` com payment                                                              |
| G3     | Badge coletivo: duplica para `player_badges` (por jogador) + `team_badges` (por time)                                                                  |
| G6     | `category_id` nullable apenas em `championships`; não em `team_sport_modes`                                                                            |
| G8/G13 | Temporada = ano-calendário via campo `year` INT; sem tabela `seasons`                                                                                  |
| G11    | Laravel Notifications (Fase 1); `device_tokens` diferido para Fase 8                                                                                   |
| G14    | Adicionar `expired` ao enum `confirmation`                                                                                                             |
| G15    | Campos de pênaltis separados do placar regulamentar                                                                                                    |
| G16    | `users.slug` implementado na Fase 5                                                                                                                    |
| —      | **Preço Player Pro:** R$ 3,90/mês (simbólico)                                                                                                          |
| —      | **Limite de campeonatos Free:** 1 ativo simultâneo; Club/Liga ilimitado                                                                                |
| —      | **Histórico Free:** completo — histórico de estatísticas não é gated por plano                                                                         |
| —      | **Busca de jogadores:** pública — qualquer visitante sem login pode buscar descobríveis                                                                |
| —      | **Escopo do staff (agora):** somente leitura. **Futuro:** `head_coach` poderá definir escalação de titulares/reservas em partidas sem ser dono do time |

---

## Decisões em aberto

> Nenhuma decisão pendente. Todos os bloqueadores foram resolvidos.
