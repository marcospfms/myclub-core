# Fluxo de Amistosos — MyClub

## Modelo de estados

Um amistoso (`friendly_matches`) possui dois campos que representam dimensões distintas do seu ciclo de vida:

- **`confirmation`**: se o convite foi aceito pelo time desafiado
- **`match_status`**: estado operacional da partida em si

---

## Campo `confirmation`

| Valor | Significado |
| --- | --- |
| `pending` | Convite enviado; aguardando resposta do time desafiado |
| `confirmed` | Time desafiado aceitou o amistoso |
| `rejected` | Time desafiado recusou |

> O desafiante pode remover o convite enquanto `confirmation = pending` — sem manter histórico.
> Um amistoso `rejected` é encerrado sem gerar partida. Não produz estatísticas, não afeta rankings.
> O convite expira automaticamente após **2 dias** sem resposta. O desafiante também pode cancelá-lo manualmente antes disso.

---

## Campo `match_status`

| Valor | Significado |
| --- | --- |
| `scheduled` | Amistoso confirmado e agendado; ainda não ocorreu |
| `completed` | Partida encerrada; resultado confirmado por ambos os times |
| `cancelled` | Cancelado após confirmação; não ocorreu |
| `postponed` | Adiado; nova data a combinar |

---

## Fluxo completo

```
[Criação do amistoso]
confirmation: pending
match_status: —

        ↓ time desafiado responde

confirmation: confirmed          confirmation: rejected
match_status: scheduled          → encerrado (sem partida)

        ↓ partida acontece

match_status: completed          match_status: cancelled
(resultado + stats registrados)  (cancelado após confirmação)

                ↑
        match_status: postponed
        (possível a qualquer momento antes de completed)
```

---

## Etapas e responsabilidades

| Etapa | Ação | Quem |
| --- | --- | --- |
| 1 | Criar amistoso (definir adversário, data, local, visibilidade pública/privada) | Dono do time desafiante |
| 2 | Notificar time desafiado | Sistema (in-app web + push notification mobile) |
| 3 | Confirmar ou recusar o convite (prazo: 2 dias) | Dono do time desafiado |
| 4 | Se confirmado: amistoso fica `scheduled` | Sistema (automático) |
| 5 | Alterar data se necessário (`postponed`) | Dono de qualquer um dos dois times |
| 6 | Registrar resultado (`home_goals`, `away_goals`) | Dono do time desafiante ou desafiado |
| 7 | Confirmar o resultado registrado | O outro time (aquele que não registrou) |
| 8 | Registrar estatísticas individuais (`performance_highlights`) | Cada dono registra os seus próprios jogadores |
| 9 | Encerrar amistoso (`completed`) | Sistema (automático após confirmação bilateral do resultado) |
| 10 | Atualizar `team_stats_cache` para ambos os times | Sistema (automático) |

---

## Regras de negócio

- Estatísticas individuais (`performance_highlights`) só podem ser registradas em amistosos com `match_status = completed`
- Um amistoso `rejected` ou `cancelled` não gera dados de estatística nem impacta rankings
- Um amistoso pode passar por `postponed` quantas vezes for necessário; apenas a data atual (`scheduled_at`) é atualizada — o histórico de datas não é preservado
- `home_notes` e `away_notes` são observações livres de cada time, visíveis apenas para o dono do respectivo time
- O resultado de um amistoso exige **confirmação bilateral**: qualquer dos dois donos registra o placar; o outro confirma; só então o amistoso passa para `completed`
- Após `completed`, nenhuma edição do resultado é possível
- Amistosos não existem entre o mesmo time em modalidades diferentes (não há amistoso interno)

---

## Confirmação bilateral de resultado

O modelo adota confirmação bilateral ("handshake") para garantir integridade do placar:

1. Qualquer dos dois donos registra `home_goals` e `away_goals`
2. O outro time recebe notificação e deve confirmar ou contestar o resultado
3. Se confirmar: `match_status` passa para `completed` automaticamente
4. Se contestar: o resultado volta a `pending_result` e ambos os times precisam negociar (campo `result_status`)

**Campos a adicionar ao schema:**

| Campo | Tabela | Tipo | Observação |
| --- | --- | --- | --- |
| `is_public` | `friendly_matches` | boolean | default `false` — definido pelo desafiante na criação |
| `result_status` | `friendly_matches` | enum | `none` / `pending` / `confirmed` / `disputed` |
| `result_registered_by` | `friendly_matches` | bigint FK → `users` | quem inputou o placar primeiro |

---

## Decisões resolvidas

- **Notificações:** in-app (web) + push notification (mobile)
- **Prazo de resposta ao convite:** 2 dias; após isso o convite expira automaticamente; desafiante pode cancelar antes
- **Edição do resultado:** qualquer dos dois times pode registrar; o outro confirma; após `completed` não há edição
- **Amistoso interno:** não existe — um time não pode se desafiar em modalidades diferentes
- **Confirmação bilateral:** implementada via `result_status`
