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
| Remover convite pendente (sem confirmation) | Dono do time desafiante — sem manter histórico |
| Confirmar o amistoso | Dono do time desafiado |
| Recusar o amistoso | Dono do time desafiado |
| Definir visibilidade (público ou privado) | Dono do time desafiante (no momento da criação) |
| Registrar resultado (`home_goals`, `away_goals`) | Desafiante registra; desafiado deve confirmar. Desafiado também pode registrar o resultado — desafiante confirma. |
| Registrar estatísticas individuais (`performance_highlights`) | Cada dono registra as estatísticas dos seus próprios jogadores |
| Encerrar amistoso (`completed`) | Sistema (automático após confirmação bilateral do resultado) |
| Cancelar amistoso (`cancelled`) | Dono de qualquer um dos dois times |
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
| Editar escalação | Somente dono do time (`teams.owner_id`) |
| Registrar resultado de partida | Somente dono do time (`teams.owner_id`) |

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
| `is_public` | `friendly_matches` | boolean | Desafiante define se o amistoso é visível publicamente |

---

## Decisões em aberto

- **Escopo de ação da comissão técnica:** membros do staff têm acesso de leitura ao elenco e escalação. Edição de escalação e registro de resultados são exclusivos do dono do time — não há diferença por papel interno da comissão.
