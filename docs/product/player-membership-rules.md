# Regras de Vínculo de Jogadores — MyClub

## Conceito de vínculo

Um jogador (`players`) se vincula a um time por meio de `player_memberships`, que relaciona:

- `player_id` → FK → `players` (o atleta)
- `team_sport_mode_id` → FK → `team_sport_modes` (o time em uma modalidade específica)
- `position_id` → posição jogada naquele vínculo
- `is_starter` → é titular?

O vínculo é **por time e por modalidade** — o mesmo jogador pode ter papéis e posições diferentes dependendo da combinação.

---

## Regras de pertencimento

### Adição ao elenco requer aprovação do atleta

Para adicionar um jogador a um time, o dono do time envia um convite. O atleta precisa aceitar antes de aparecer no elenco ativo. Saída do time é sempre livre — qualquer jogador pode sair sem aprovação do dono do time.

### Múltiplos times simultaneamente

Um jogador **pode** pertencer a múltiplos times ao mesmo tempo. Não há restrição no schema que impeça isso.

Cenários válidos:
- Jogador que participa do racha de quarta (Time A) e do campeonato de domingo (Time B)
- Jogador que representa times diferentes em cidades distintas

O sistema não faz checagem de conflito de agenda nem impõe exclusividade de vínculo. Não há limite máximo de times por jogador nem de jogadores por elenco.

### Mesmo time, múltiplas modalidades

Um jogador pode ter `player_memberships` em diferentes modalidades do mesmo time. Exemplo: o time "Amigos do Bairro" participa de Campo e Futsal; João está vinculado aos dois `team_sport_modes` desse mesmo time.

### Posição é definida por vínculo, não por perfil global

A posição (`position_id`) é definida **no vínculo**, não no perfil global do jogador. Isso permite que João seja ZAGUEIRO no Campo e FIXO no Futsal, mesmo sendo pelo mesmo time.

---

## Ciclo de vida do vínculo

| Estado | Condição | Campo no schema |
| --- | --- | --- |
| Ativo | Jogador no elenco atual | `left_at = null` |
| Histórico | Jogador saiu ou foi removido | `left_at = timestamp` |

### Desvinculação

Ao remover um jogador do elenco, o registro em `player_memberships` **não deve ser apagado**. O campo `left_at` deve ser preenchido com a data da saída.

Isso preserva:
- Estatísticas históricas em `performance_highlights` e `championship_match_highlights`
- Badges conquistados durante o período no time
- Pontuação de campeonatos encerrados
- O histórico de pertencimento do jogador para fins de ranking e carreira

### Participação em partidas após desvinculação

Um jogador com `left_at` preenchido:
- **Não pode** ser escalado em partidas futuras daquele `team_sport_mode_id`
- **Pode** ser consultado em histórico de partidas passadas normalmente

---

## Reativação de vínculo

Quando `left_at` está preenchido e o jogador retorna ao mesmo time, existem duas abordagens:

**Opção A — Criar novo `player_membership`** (recomendada)

| Prós | Contras |
| --- | --- |
| Histórico separado e delimitado por período | Dois registros para o mesmo jogador no mesmo time |
| Consultas de "ativo" são simples (`left_at = null`) | Relatórios de carreira precisam agregar múltiplos vínculos |
| Nunca há ambiguidade sobre quando o jogador saiu e voltou | |  
| Auditoria completa preservada | |

**Opção B — Zerar o `left_at` do vínculo anterior**

| Prós | Contras |
| --- | --- |
| Estrutura mais simples: sempre um único registro por (player, team_sport_mode) | Perde a informação de que houve uma ausência |
| | O período fora do time desaparece do histórico |
| | Auditoria prejudicada |

> **Recomendação:** Opção A. Preserva o histórico completo com datas de entrada e saída de cada período, o que é essencial para cálculos de carreira e badges como `loyal_player`.

---

## Participação em campeonatos

Para que um jogador possa ser escalado em partidas de um campeonato:

1. O `team_sport_mode_id` deve estar inscrito no campeonato (`championship_teams`)
2. O jogador deve ter sido selecionado pelo dono do time na inscrição do campeonato (`championship_team_players`)
3. O jogador deve ter um `player_membership` ativo (sem `left_at`) naquele `team_sport_mode_id`

**Adição ao elenco após início do campeonato:** o dono pode adicionar novos jogadores ao elenco do time a qualquer momento. Porém, apenas os jogadores selecionados na inscrição podem jogar — não é possível adicionar um jogador à lista de disputão do campeonato após o início.

Se o jogador for desvinculado durante o campeonato:
- Estatísticas já registradas são mantidas
- Ele não pode ser escalado em partidas futuras do campeonato

---

## Histórico entre times

O histórico de estatísticas de um jogador em outros times é visível apenas se o próprio atleta permitir. Essa é uma configuração do perfil do jogador (`players.history_public`). Por padrão, o histórico é privado.

---

## Campos a adicionar ao schema

| Campo | Tabela | Tipo | Observação |
| --- | --- | --- | --- |
| `left_at` | `player_memberships` | timestamp | nullable — `null` = vínculo ativo; preenchido = histórico |
| `history_public` | `players` | boolean | default `false` — controla visibilidade do histórico entre times |

---

## Decisões resolvidas

- **Adição ao time:** requer aprovação do atleta via convite
- **Saída do time:** qualquer jogador pode sair livremente; sem aprovação
- **Histórico visível:** controlado pelo próprio atleta via `history_public`
- **Limite de elenco:** sem limite máximo de jogadores por time
- **Reativação:** cria novo `player_membership` (Opção A)
- **Entrada durante campeonato:** pode entrar no elenco a qualquer momento, mas só pode jogar no campeonato se estava na lista de inscrição
