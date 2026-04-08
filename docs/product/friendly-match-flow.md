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

> Um amistoso `rejected` é encerrado sem gerar partida. Não produz estatísticas, não afeta rankings.

---

## Campo `match_status`

| Valor | Significado |
| --- | --- |
| `scheduled` | Amistoso confirmado e agendado; ainda não ocorreu |
| `completed` | Partida encerrada; resultado e estatísticas registrados |
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
| 1 | Criar amistoso (definir adversário, data, local) | Dono do time desafiante |
| 2 | Notificar time desafiado | Sistema |
| 3 | Confirmar ou recusar o convite | Dono do time desafiado |
| 4 | Se confirmado: amistoso fica `scheduled` | Sistema (automático) |
| 5 | Alterar data se necessário (`postponed`) | Dono de qualquer um dos dois times |
| 6 | Registrar resultado (`home_goals`, `away_goals`) | Dono de qualquer um dos dois times |
| 7 | Registrar estatísticas individuais (`performance_highlights`) | Dono de qualquer um dos dois times |
| 8 | Encerrar amistoso (`completed`) | Dono de qualquer um dos dois times |
| 9 | Atualizar `team_stats_cache` para ambos os times | Sistema (automático) |

---

## Regras de negócio

- Estatísticas individuais (`performance_highlights`) só podem ser registradas em amistosos com `match_status = completed`
- Um amistoso `rejected` ou `cancelled` não gera dados de estatística nem impacta rankings
- Um amistoso pode passar por `postponed` quantas vezes for necessário; apenas a data atual (`scheduled_at`) é atualizada — o histórico de datas não é preservado
- `home_notes` e `away_notes` são observações livres de cada time, visíveis apenas para o dono do respectivo time
- Um amistoso `completed` não pode ter seu resultado editado sem intervenção de `admin`

---

## Campos relacionados à confirmação bilateral

O campo `confirmation` em `friendly_matches` representa apenas a resposta do time desafiado. O time desafiante, ao criar o amistoso, já confirma implicitamente.

Possível evolução: se ambos os times precisarem confirmar o resultado (modelo "handshake"), um campo `away_confirmation` poderia ser adicionado. Não está no escopo atual.

---

## Decisões em aberto

- **Notificações:** como o time desafiado é notificado do convite? (in-app, e-mail, ou apenas em dashboard?)
- **Prazo de resposta:** existe expiração automática do convite `pending`? Se sim, após quantos dias o amistoso é automaticamente `cancelled`?
- **Edição de resultado por dono de time:** pode o dono editar `home_goals`/`away_goals` após `completed`? Ou apenas `admin` pode corrigir?
- **Amistoso interno:** pode um time criar um amistoso contra si mesmo em modalidades diferentes (ex: time A no Campo vs time A no Futsal)?
- **Confirmação bilateral de resultado:** deve existir um mecanismo onde ambos os times confirmam o placar antes de `completed`, ou basta o registro unilateral?
