# Regras de Autorização — MyClub

## Princípio geral

A autorização no MyClub combina duas dimensões:

1. **Papel global (`users.role`)**: `admin` tem acesso irrestrito ao sistema; `user` é o default para todos os outros
2. **Relação com o recurso**: o que o usuário pode fazer depende do vínculo dele com o objeto — é dono do time? está no elenco? foi adicionado ao staff?

Não existe um sistema de permissões granular por papel além do `admin/user`. O controle é feito por **ownership**: quem criou ou é referenciado como responsável é quem pode gerenciar.

---

## Times (`teams`)

| Ação | Quem pode |
| --- | --- |
| Criar time | Qualquer `user` autenticado (respeita limite de plano) |
| Editar nome / descrição / escudo | `teams.owner_id` ou `admin` |
| Excluir time | `teams.owner_id` ou `admin` |
| Ver perfil público do time | Qualquer visitante (anônimo ou autenticado) |
| Gerenciar elenco (adicionar/remover jogadores) | `teams.owner_id` ou `admin` |
| Gerenciar comissão técnica | `teams.owner_id` ou `admin` |
| Transferir ownership do time | `admin` apenas |

---

## Jogadores no Elenco (`player_memberships`)

| Ação | Quem pode |
| --- | --- |
| Adicionar jogador ao elenco | Dono do time (`teams.owner_id`) |
| Remover jogador do elenco | Dono do time ou o próprio jogador |
| Alterar posição / is_starter | Dono do time |
| Ver elenco | Qualquer visitante |

> Remover o próprio jogador do elenco é permitido para garantir autonomia do atleta. A remoção não apaga o histórico — apenas registra `left_at` no vínculo.

---

## Amistosos (`friendly_matches`)

| Ação | Quem pode |
| --- | --- |
| Criar (desafiar outro time) | Dono do time desafiante |
| Confirmar o amistoso | Dono do time desafiado |
| Recusar o amistoso | Dono do time desafiado |
| Registrar resultado (`home_goals`, `away_goals`) | Dono de qualquer um dos dois times |
| Registrar estatísticas individuais | Dono de qualquer um dos dois times |
| Encerrar amistoso (`completed`) | Dono de qualquer um dos dois times |
| Cancelar amistoso (`cancelled`) | Dono do time criador ou `admin` |
| Adiar amistoso (`postponed`) | Dono de qualquer um dos dois times |

---

## Campeonatos (`championships`)

| Ação | Quem pode |
| --- | --- |
| Criar campeonato | Qualquer `user` autenticado (respeita limite de plano) |
| Editar campeonato | `championships.created_by` ou `admin` |
| Excluir campeonato | `championships.created_by` ou `admin` — apenas se `status = draft` |
| Avançar estado do campeonato | `championships.created_by` ou `admin` |
| Inscrever time | Dono do time (o time já deve estar na modalidade do campeonato) |
| Remover time inscrito | `championships.created_by` ou `admin` |
| Registrar resultado de partida | `championships.created_by` ou `admin` |
| Encerrar campeonato e distribuir prêmios | `championships.created_by` ou `admin` |
| Ver campeonato público | Qualquer visitante |
| Ver campeonato privado | Times participantes e `championships.created_by` |

> **Campo a adicionar ao schema:** `championships.created_by` (FK → `users`). O schema atual não registra o criador do campeonato, o que impossibilita a autorização de operações de gestão. Ver `docs/database/schema.md` — observações de modelagem.

---

## Comissão Técnica (`staff_members`, `team_staff`)

| Ação | Quem pode |
| --- | --- |
| Adicionar membro à comissão | Dono do time (`teams.owner_id`) |
| Remover membro da comissão | Dono do time ou o próprio membro |
| Ver escalação e elenco | Todos os membros da comissão vinculados ao time |
| Editar escalação | **A definir** — ver decisões em aberto |
| Registrar resultado de partida | **A definir** — ver decisões em aberto |

---

## Badges (`player_badges`)

| Ação | Quem pode |
| --- | --- |
| Conceder badge automaticamente | Sistema (ao encerrar campeonato ou detectar evento de carreira) |
| Conceder badge manualmente | `admin` apenas |
| Revogar badge | `admin` apenas |
| Ver badges | Qualquer visitante |

---

## Catálogos administrativos (`sport_modes`, `categories`, `positions`, `formations`, `staff_roles`, `badge_types`)

| Ação | Quem pode |
| --- | --- |
| Criar / editar / excluir entradas de catálogo | `admin` apenas |
| Ver catálogos | Qualquer usuário autenticado |

---

## Campos a adicionar ao schema

| Campo | Tabela | Tipo | Motivo |
| --- | --- | --- | --- |
| `created_by` | `championships` | bigint FK → `users` | Necessário para autorizar operações de gestão |

---

## Decisões em aberto

- **Escopo de ação da comissão técnica:** membros do staff devem ter poder de editar escalação e registrar resultados? Se sim, todos os papéis (`head_coach`, `physiotherapist`, `doctor`) têm o mesmo escopo, ou deve ser granularizado por papel?
- **Visibilidade de amistosos:** amistosos devem ser visíveis publicamente ou apenas para os donos dos times participantes?
- **Co-gestão de time:** pode existir mais de um `owner` por time? (ex: sócio fundador e gerente)
- **Transferência de ownership:** quem pode promover outro usuário a dono do time?
