# Ciclo de Vida de Campeonatos — MyClub

## Estados de um campeonato

O campo `championships.status` define em que fase o campeonato se encontra:

| Estado | Descrição |
| --- | --- |
| `draft` | Criado, mas não aberto para inscrições. Pode ser editado livremente: formato, fases, grupos. |
| `enrollment` | Aberto para inscrição de times. Configuração de fases já definida; times sendo adicionados. |
| `active` | Campeonato em andamento. Partidas sendo registradas, resultado não pode mais ser editado retroativamente sem intervenção de admin. |
| `finished` | Todas as partidas encerradas. Prêmios calculados e distribuídos. `team_stats_cache` atualizado. |
| `archived` | Histórico somente-leitura. Nenhuma edição permitida. |
| `cancelled` | Cancelado antes de concluir. Times não recebem pontuação de ranking pelas partidas do campeonato. |

---

## Transições válidas

```
draft → enrollment → active → finished → archived

draft → cancelled
enrollment → cancelled
active → cancelled  (apenas admin)
```

| De | Para | Quem dispara | Pré-condição |
| --- | --- | --- | --- |
| `draft` | `enrollment` | Criador do campeonato | Pelo menos uma fase e uma modalidade configuradas |
| `enrollment` | `active` | Criador do campeonato | Mínimo absoluto de times inscritos respeitado (ver formatos) |
| `active` | `finished` | Sistema (automático) ou criador | Todas as partidas com `match_status = completed` ou `cancelled` |
| `finished` | `archived` | Sistema (automático após N dias) | Nenhuma — transição de housekeeping |
| `*` | `cancelled` | Criador (`draft`/`enrollment`) ou `admin` (`active`) | Campeonatos `finished` não podem ser cancelados |

> Cancelar um campeonato `active` é uma operação destrutiva — disponível apenas para `admin` por esse motivo.

---

## Regras por tipo de fase (`championship_phases.type`)

### Fase de grupos (`group_stage`)

- Times são distribuídos manualmente ou automaticamente em grupos (`championship_groups`)
- Cada grupo gera rodadas (`championship_rounds`) com todas as combinações de jogos (todos contra todos)
- Ao encerrar a fase, `championship_group_entries.final_position` é preenchido com a posição final de cada time no grupo
- Os N primeiros classificados de cada grupo avançam para a fase seguinte
- O número de classificados por grupo é configurável por fase

### Fase eliminatória (`knockout`)

- Chaveamento gerado automaticamente a partir das posições da fase anterior (ou inserção manual)
- `legs = 1`: jogo único — time com mais gols avança; **empate na fase eliminatória define vencedor em pênaltis**
- `legs = 2`: ida e volta — saldo de gols determina o classificado; **empate no saldo de gols ao final da partida de volta define vencedor em pênaltis**
- O registro dos pênaltis é feito pelo organizador do campeonato após o encerramento da partida

---

## Cronograma de eventos ao encerrar (`active → finished`)

A transição `finished` dispara os seguintes eventos na ordem:

1. Verificar que todas as `championship_matches` estão com `match_status = completed` ou `cancelled`
2. Calcular e persistir `championship_awards` (artilheiro, bola de ouro, melhor goleiro, fair play, garçom) com base em `championship_match_highlights`
3. Conceder `player_badges` vinculados ao campeonato para os vencedores de cada prêmio
   - Badges de escopo `championship` (ex: `top_scorer`, `golden_ball`, `iron_man`) são atribuídos ao **perfil do jogador** e incluem o nome do campeonato no campo `notes`
   - Na **Fase 3**, apenas badges individuais em `player_badges` são concedidos automaticamente
   - Badges de conquista coletiva (ex: `unbeaten_champion`) ficam para a fase posterior de campeonatos avançados
4. Detectar badges de escopo `career` disparados por eventos do campeonato (ex: `hat_trick`, `mvp_streak`, `loyal_player`)
   - Esses badges também exibem o campeonato de origem no campo `notes` quando aplicável
5. Atualizar `team_stats_cache` para todos os times participantes
6. Incrementar `championship_titles` no cache do time campeão

> Na implementação atual da **Fase 3**, os passos 5 e 6 permanecem diferidos para a fase de rankings e cache.

> A ordem importa: badges dependem de `championship_awards`, que dependem de `championship_match_highlights` completos.

---

## Inscrição de times e seleção de jogadores

Ao criar o campeonato, o organizador define:

- **Número máximo de jogadores por time** na disputa (ex: 20 jogadores por inscrição)

Ao inscrever o time no campeonato, o dono do time deve:

1. Selecionar quais jogadores do elenco participarão da disputa — até o máximo definido pelo campeonato
2. Pode levar menos jogadores do que o máximo permitido
3. Apenas os jogadores selecionados podem ser escalados em partidas daquele campeonato

O organizador do campeonato têm acesso à lista de jogadores definida por cada time no momento da inscrição.

> O limite é armazenado em `championships.max_players`. A tabela `championship_team_players` registra quais jogadores cada time levou para a disputa.

---

## Formatos e mínimos de inscrição

A transição `enrollment → active` é bloqueada se o número de times inscritos for inferior ao mínimo absoluto do formato:

| Formato | Campo `format` | Mínimo absoluto | Recomendado |
| --- | --- | --- | --- |
| Pontos Corridos | `league` | 3 | 6–12 |
| Mata-mata simples | `knockout` | 4 | 8, 16, 32 |
| Mata-mata ida e volta | `knockout` | 4 | 8, 16 |
| Copa (grupos + mata-mata) | `cup` | 8 | 16, 32 |

---

## Campos a adicionar ao schema

| Campo | Tabela | Tipo | Observação |
| --- | --- | --- | --- |
| `status` | `championships` | enum | `draft` / `enrollment` / `active` / `finished` / `archived` / `cancelled` |
| `created_by` | `championships` | bigint FK → `users` | Necessário para autorização de gestão |
| `advances_count` | `championship_phases` | int | Número de classificados por grupo que avançam para a próxima fase |
| `max_players` | `championships` | int | Número máximo de jogadores que cada time pode levar para a disputa |
| `championship_team_players` | nova tabela | pivot | Jogadores selecionados por cada time para o campeonato |

> Esses campos e tabelas-base já fazem parte do schema atual da Fase 3.

---

## Decisões resolvidas

- **Critério de desempate em `knockout`:** pênaltis em qualquer empate fora da fase de grupos
- **Número de classificados por grupo:** configurável por fase via `advances_count` no `championship_phases`
- **Cancelamento de campeonato:** ao cancelar, todos os dados do campeonato são descartados — resultados não impactam ranking nem geram badges
- **Edição retroativa:** após `finished`, nenhuma edição é permitida mesmo por `admin`
- **Notificações de estado do campeonato:** não implementadas
- **Prazo para arquivamento:** 7 dias após `finished` o campeonato passa para `archived`; permanece visível como histórico somente leitura
