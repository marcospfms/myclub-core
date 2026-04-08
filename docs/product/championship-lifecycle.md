# Ciclo de Vida de Campeonatos — MyClub

## Estados de um campeonato

O campo `championships.status` (a adicionar ao schema) define em que fase o campeonato se encontra:

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
- `legs = 1`: jogo único — time com mais gols avança; empate resolve via critério de desempate (ver decisões em aberto)
- `legs = 2`: ida e volta — saldo de gols determina o classificado; em caso de empate no saldo, critério de desempate é aplicado

---

## Cronograma de eventos ao encerrar (`active → finished`)

A transição `finished` dispara os seguintes eventos na ordem:

1. Verificar que todas as `championship_matches` estão com `match_status = completed` ou `cancelled`
2. Calcular e persistir `championship_awards` (artilheiro, bola de ouro, melhor goleiro, fair play, garçom) com base em `championship_match_highlights`
3. Conceder `player_badges` vinculados ao campeonato (`championship_id`) para os vencedores de cada prêmio
4. Detectar badges de escopo `career` disparados por eventos do campeonato (ex: `hat_trick`, `iron_man`, `unbeaten_champion`)
5. Atualizar `team_stats_cache` para todos os times participantes
6. Incrementar `championship_titles` no cache do time campeão

> A ordem importa: badges dependem de `championship_awards`, que dependem de `championship_match_highlights` completos.

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

---

## Decisões em aberto

- **Critério de desempate em `knockout` com `legs = 1`:** pênaltis embutidos no sistema? Gol extra? Configurável por campeonato?
- **Número de classificados por grupo:** configurável no `championship_phases` (ex: top 2 de cada grupo avançam) — campo a adicionar
- **Cancelamento de campeonato `active`:** os resultados parciais devem ser descartados do `team_stats_cache` ou preservados?
- **Edição retroativa:** um `admin` pode corrigir um resultado já registrado? Se sim, quais eventos precisam ser re-disparados?
- **Notificações:** times participantes devem ser notificados ao campeonato avançar de estado?
- **Prazo para arquivamento automático:** quantos dias após `finished` o campeonato vai para `archived`?
