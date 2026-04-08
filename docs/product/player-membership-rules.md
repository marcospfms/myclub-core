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

### Múltiplos times simultaneamente

Um jogador **pode** pertencer a múltiplos times ao mesmo tempo. Não há restrição no schema que impeça isso.

Cenários válidos:
- Jogador que participa do racha de quarta (Time A) e do campeonato de domingo (Time B)
- Jogador que representa times diferentes em cidades distintas

O sistema não faz checagem de conflito de agenda nem impõe exclusividade de vínculo.

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

## Participação em campeonatos

Para que um jogador possa ser escalado em partidas de um campeonato:

1. O `team_sport_mode_id` deve estar inscrito no campeonato (`championship_teams`)
2. O jogador deve ter um `player_membership` ativo (sem `left_at`) naquele `team_sport_mode_id`

Se o jogador for desvinculado durante o campeonato:
- Estatísticas já registradas são mantidas
- Ele não pode ser escalado em partidas futuras do campeonato

> **Decisão em aberto:** pode um dono de time adicionar um jogador ao elenco após o campeonato já ter iniciado? Se sim, existe um limite de rodadas (ex: não pode ser adicionado após a rodada 2)?

---

## Campos a adicionar ao schema

| Campo | Tabela | Tipo | Observação |
| --- | --- | --- | --- |
| `left_at` | `player_memberships` | timestamp | nullable — `null` = vínculo ativo; preenchido = histórico |

---

## Decisões em aberto

- **Transferência formal:** existe um mecanismo de "pedido de saída" que o dono do time deve aprovar antes do `left_at` ser registrado, ou qualquer jogador pode sair livremente?
- **Histórico visível entre times:** o proprietário do Time B pode ver que um jogador já jogou no Time A e quais foram suas estatísticas lá?
- **Entrada durante campeonato:** jogador pode entrar no elenco após início do campeonato? Com restrição de rodadas?
- **Limite máximo de jogadores por elenco:** o legado tinha limite por time — manter esse controle, e qual é o número para o plano Free?
- **Reativação de vínculo:** se um jogador saiu e quer voltar ao mesmo time, cria-se um novo `player_membership` ou o `left_at` do anterior é zerado?
