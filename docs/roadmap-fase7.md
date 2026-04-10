# Roadmap Fase 7 — Campeonatos Avançados (knockout, cup)

> Detalhamento completo de implementação da Fase 7. Cobertura: services, form requests, resources, policy, controllers, rotas, types TypeScript, factories e testes.
>
> **Pré-requisito:** Fase 3 concluída (schema completo de campeonatos disponível; `championship_phases`, `championship_groups`, `championship_rounds`, `championship_matches` já existem). Fase 4 concluída (`PlanGatingService` ativo para gate Club+). Fase 6 concluída (`TeamStatsCacheService` disponível para título ao encerrar a fase final).
>
> Referências de schema: `docs/database/schema.md` §4 (Campeonatos) — especialmente `championship_phases`, `championship_groups`, `championship_group_entries`, `championship_rounds`, `championship_matches`.
> Referências de produto: `docs/product/feature-gating.md`, `docs/product/authorization-rules.md`, `docs/business/championship-lifecycle.md`.
> Referências de padrões: `docs/patterns/`.

---

## 1. Objetivo

Estender o sistema de campeonatos para suportar os formatos **`knockout`** (mata-mata com chaveamento automático) e **`cup`** (grupos + mata-mata), disponíveis para o plano **Club e superior**. Implementar o avanço automático de times entre fases e a concessão de **badges coletivos** ao time campeão. Resolve o gap G3 (modelagem de badges coletivos de time).

---

## 2. Escopo

| Entrega                                | Descrição                                                                                                   |
| -------------------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `ChampionshipBracketService`           | Gera `championship_rounds` e `championship_matches` para uma fase knockout                                  |
| `ChampionshipPhaseAdvancementService`  | Calcula classificados de fase de grupos e promove para a fase knockout seguinte                             |
| Atualização `ChampionshipService`      | Cria campeonatos knockout/cup com gating Club+; encerra multi-fase; distribui badges coletivos (resolve G3) |
| Atualização `StoreChampionshipRequest` | Aceita `format = knockout \| cup` e campos de configuração de fases                                         |
| Atualização `ChampionshipResource`     | Expõe `phases` com type/legs/advances_count; campo `bracket` quando knockout                                |
| Atualização `ChampionshipPolicy`       | Gate Club+ na criação de knockout/cup                                                                       |
| Atualização `ChampionshipController`   | Endpoint de avanço de fase (`POST .../advance-phase`)                                                       |
| Rotas API                              | `POST /api/v1/championships/{id}/advance-phase`                                                             |
| Types TypeScript                       | `ChampionshipPhase`, `ChampionshipBracket`, `BracketMatch`                                                  |
| Factories atualizadas                  | `ChampionshipPhaseFactory`, `ChampionshipGroupFactory`, `ChampionshipGroupEntryFactory`                     |
| Testes Feature (4 classes)             | Criação knockout/cup, geração de chaveamento, avanço de fase, badges coletivos                              |

### Progresso atual

⬜ Nenhum bloco desta fase foi iniciado.

---

## 3. Decisões de modelagem

### 3.1 Gate de plano: Club+ para knockout e cup

Formatos `knockout` e `cup` requerem plano **Club ou superior**. Formato `league` permanece disponível no Free (com limite de 1 campeonato ativo). A verificação é feita em `ChampionshipPolicy::create()` via `PlanGatingService`.

### 3.2 Chaveamento automático (`knockout`)

O bracket é gerado pelo `ChampionshipBracketService` a partir de uma lista ordenada de times (por seeding manual ou por `final_position` dos grupos). O algoritmo:

1. Recebe `N` times ordenados por semente (semente 1 = melhor classificado)
2. Calcula a potência de 2 imediatamente superior a `N` → `P` (ex: 6 times → P = 8; 8 times → P = 8)
3. Times ausentes para completar `P` recebem bye (match com `home_team_id = null` ou `away_team_id = null`)
4. Cria `championship_rounds` sequencialmente: "Oitavas", "Quartas", "Semifinal", "Final"
5. Na primeira rodada, cria os `championship_matches` pareando semente 1 × última semente, 2 × penúltima, etc. (padrão FIFA)
6. Rodadas subsequentes são criadas com placeholders (`home_team_id = null`) — preenchidas conforme avançamento

> **Legs:** se `phase.legs = 2`, cada confronto gera dois `championship_matches` (leg 1 e leg 2). Nesta fase, o sistema cria apenas o leg 1; o leg 2 é criado pelo `ChampionshipPhaseAdvancementService` após o resultado do leg 1.

### 3.3 Formato `cup`: grupos + mata-mata

Um campeonato `cup` tem duas fases:

- **Fase 1** (`type = group_stage`): gerada como Fase 3 (pontos corridos por grupo)
- **Fase 2+** (`type = knockout`): gerada pelo `ChampionshipBracketService` com os times classificados da fase de grupos

O organizador configura no momento da criação:

- número de grupos
- times por grupo
- `advances_count` (quantos classificam por grupo para a fase knockout)

A transição entre fases é disparada explicitamente pelo organizador via `POST /api/v1/championships/{id}/advance-phase`, que chama `ChampionshipPhaseAdvancementService`.

### 3.4 Avanço de fase (`advance-phase`)

O endpoint `advance-phase`:

1. Localiza a fase atual (menor `phase_order` com partidas pendentes)
2. Verifica se todas as partidas da fase estão `completed` ou `cancelled`
3. Calcula a classificação final dos grupos (`championship_group_entries.final_position`)
4. Coleta os `advances_count` primeiros de cada grupo
5. Chama `ChampionshipBracketService` para gerar a próxima fase knockout com os classificados

Se não existir próxima fase → o campeonato é encerrado (`finished`).

### 3.5 Badges coletivos — resolve G3

Ao encerrar um campeonato, além dos badges individuais já existentes (Fase 3), o sistema verifica e concede:

| Badge               | Critério                                                        | Escopo         |
| ------------------- | --------------------------------------------------------------- | -------------- |
| `unbeaten_champion` | Time campeão com zero derrotas em todo o campeonato             | `championship` |
| `clean_sweep`       | Time campeão que venceu **todas** as partidas da fase de grupos | `championship` |

Esses badges são concedidos a **todos os jogadores de `championship_team_players`** do time campeão, com `championship_id` registrado em `player_badges`.

> O campeão é identificado pelo time com `final_position = 1` na fase de grupos encerrada (league) ou pelo vencedor da partida final (knockout/cup).

### 3.6 Empate em mata-mata e pênaltis

Em uma fase `knockout` com `legs = 1`:

- O time com mais gols avança
- Em caso de empate no placar: `home_penalties > away_penalties` → home avança; caso contrário → away avança

Em uma fase com `legs = 2`:

- Soma de gols do aggregate decide
- Em caso de empate no aggregate: `home_penalties > away_penalties` do leg 2 → home avança (o time visitante no leg 2 é o mandante original)

### 3.7 Sem migração nova nesta fase

O schema completo de campeonatos (`championship_phases`, `championship_groups`, `championship_group_entries`, `championship_rounds`, `championship_matches`) já foi criado na Fase 3. Esta fase implementa apenas a lógica de serviço para os formatos knockout e cup.

---

## 4. Contexto de Domínio

```
championships
 ├── format: 'knockout' | 'cup'          ← exige Club+ (novo gate nesta fase)
 └── championship_phases (1..N, phase_order)
      ├── type: 'group_stage'            ← Fase 3 (já implementado)
      │    ├── championship_groups       ← Fase 3
      │    │    └── championship_group_entries (final_position)
      │    └── championship_rounds → championship_matches
      └── type: 'knockout'               ← NOVO nesta fase
           └── championship_rounds        ← gerados por ChampionshipBracketService
                └── championship_matches  ← home/away gerados; próximas rodadas com null placeholders
                     ├── home_penalties / away_penalties (empate em jogo único)
                     └── leg: 1 | 2 (quando legs=2)

Badges coletivos (ao encerrar qualquer formato):
  → ChampionshipService::finish()
      → identifica time campeão
      → para cada player em championship_team_players do time campeão:
          → BadgeService::awardIfEligible(player, 'unbeaten_champion', championship)
          → BadgeService::awardIfEligible(player, 'clean_sweep', championship)
```

---

## 5. Services

Localização: `app/Services/Championships/`

| Service                               | Arquivo                                                              |
| ------------------------------------- | -------------------------------------------------------------------- |
| `ChampionshipBracketService`          | `app/Services/Championships/ChampionshipBracketService.php`          |
| `ChampionshipPhaseAdvancementService` | `app/Services/Championships/ChampionshipPhaseAdvancementService.php` |
| `ChampionshipService`                 | `app/Services/Championships/ChampionshipService.php` (atualizado)    |

### 5.1 `ChampionshipBracketService`

```php
class ChampionshipBracketService
{
    /**
     * Gera as rodadas e partidas de uma fase knockout a partir de uma lista ordenada de times.
     *
     * @param  ChampionshipPhase  $phase     Fase knockout a ser populada
     * @param  array<int>         $seededTeamIds  IDs de team_sport_mode ordenados por semente (1 = melhor)
     */
    public function generate(ChampionshipPhase $phase, array $seededTeamIds): void
    {
        DB::transaction(function () use ($phase, $seededTeamIds) {
            $bracketSize = $this->nextPowerOfTwo(count($seededTeamIds));
            // Preencher com null (bye) até atingir potência de 2
            $paddedTeams = array_pad($seededTeamIds, $bracketSize, null);

            $roundCount = (int) log($bracketSize, 2); // ex: 8 times → 3 rodadas
            $this->createRoundsAndFirstRoundMatches($phase, $paddedTeams, $roundCount);
        });
    }

    private function createRoundsAndFirstRoundMatches(
        ChampionshipPhase $phase,
        array $paddedTeams,
        int $roundCount
    ): void {
        $roundLabels = $this->roundLabels($roundCount);
        $rounds      = [];

        for ($i = 1; $i <= $roundCount; $i++) {
            $rounds[$i] = ChampionshipRound::create([
                'championship_phase_id' => $phase->id,
                'name'                  => $roundLabels[$i] ?? "Rodada {$i}",
                'round_number'          => $i,
            ]);
        }

        // Primeira rodada: parear por semente (1 vs última, 2 vs penúltima…)
        $n       = count($paddedTeams);
        $matches = [];

        for ($i = 0; $i < $n / 2; $i++) {
            $home = $paddedTeams[$i];
            $away = $paddedTeams[$n - 1 - $i];

            $matchData = [
                'championship_round_id' => $rounds[1]->id,
                'home_team_id'          => $home,
                'away_team_id'          => $away,
                'match_status'          => 'scheduled',
                'leg'                   => 1,
            ];

            // Bye: se um dos times é null, marca como concluído automaticamente
            if ($home === null || $away === null) {
                $matchData['match_status'] = 'completed';
                $matchData['home_goals']   = $home !== null ? 1 : 0;
                $matchData['away_goals']   = $away !== null ? 1 : 0;
            }

            $matches[] = ChampionshipMatch::create($matchData);
        }

        // Rodadas posteriores: criar placeholders (null home/away)
        for ($round = 2; $round <= $roundCount; $round++) {
            $matchCount = (int) ($n / (2 ** $round));
            for ($m = 0; $m < $matchCount; $m++) {
                ChampionshipMatch::create([
                    'championship_round_id' => $rounds[$round]->id,
                    'home_team_id'          => null,
                    'away_team_id'          => null,
                    'match_status'          => 'scheduled',
                    'leg'                   => 1,
                ]);
            }
        }
    }

    /**
     * Preenche a próxima rodada com o vencedor de uma partida concluída.
     * Chamado por ChampionshipPhaseAdvancementService ao confirmar um resultado.
     */
    public function advanceWinner(ChampionshipMatch $completedMatch): void
    {
        $winner = $this->resolveWinner($completedMatch);
        if ($winner === null) {
            return; // Empate sem pênaltis definidos — não avança ainda
        }

        // Localiza a partida placeholder na próxima rodada
        $currentRoundNumber = $completedMatch->round->round_number;
        $phase              = $completedMatch->round->phase;

        $nextRound = $phase->rounds()
            ->where('round_number', $currentRoundNumber + 1)
            ->first();

        if ($nextRound === null) {
            return; // É a rodada final — o avanço é o encerramento do campeonato
        }

        // Descobre qual slot (par ou ímpar) pertence a esta partida
        $matchIndexInRound = ChampionshipMatch::where('championship_round_id', $completedMatch->championship_round_id)
            ->where('id', '<=', $completedMatch->id)
            ->count() - 1;

        $nextMatchSlot = (int) floor($matchIndexInRound / 2);

        $nextMatch = $nextRound->matches()
            ->skip($nextMatchSlot)
            ->first();

        if ($nextMatch === null) {
            return;
        }

        // Preenche home ou away dependendo de par/ímpar
        if ($matchIndexInRound % 2 === 0) {
            $nextMatch->update(['home_team_id' => $winner]);
        } else {
            $nextMatch->update(['away_team_id' => $winner]);
        }
    }

    private function resolveWinner(ChampionshipMatch $match): ?int
    {
        if ($match->home_goals > $match->away_goals) {
            return $match->home_team_id;
        }

        if ($match->away_goals > $match->home_goals) {
            return $match->away_team_id;
        }

        // Empate: verificar pênaltis
        if ($match->home_penalties !== null && $match->away_penalties !== null) {
            return $match->home_penalties > $match->away_penalties
                ? $match->home_team_id
                : $match->away_team_id;
        }

        return null; // Empate sem pênaltis definidos
    }

    private function nextPowerOfTwo(int $n): int
    {
        if ($n <= 1) {
            return 1;
        }

        return (int) (2 ** ceil(log($n, 2)));
    }

    private function roundLabels(int $roundCount): array
    {
        // Mapeia da rodada final para trás
        $labels = [1 => 'Final', 2 => 'Semifinal', 3 => 'Quartas de Final', 4 => 'Oitavas de Final'];
        $result = [];

        for ($i = $roundCount; $i >= 1; $i--) {
            $distanceFromFinal = $roundCount - $i;
            $result[$i]        = $labels[$distanceFromFinal + 1] ?? "Rodada {$i}";
        }

        return $result;
    }
}
```

### 5.2 `ChampionshipPhaseAdvancementService`

```php
class ChampionshipPhaseAdvancementService
{
    public function __construct(
        private ChampionshipBracketService $bracketService,
        private ChampionshipService $championshipService,
    ) {
    }

    /**
     * Avança o campeonato para a próxima fase.
     *
     * - Valida que todas as partidas da fase atual estão encerradas.
     * - Calcula classificados de grupos (group_stage).
     * - Gera o bracket da próxima fase knockout.
     * - Se não houver próxima fase, encerra o campeonato.
     *
     * @throws \DomainException se a fase atual ainda tem partidas pendentes
     */
    public function advance(Championship $championship, User $actor): Championship
    {
        $currentPhase = $this->resolveCurrentPhase($championship);

        if ($currentPhase === null) {
            throw new \DomainException('Nenhuma fase ativa encontrada.');
        }

        $hasPendingMatches = $currentPhase->rounds()
            ->whereHas('matches', fn ($q) =>
                $q->whereNotIn('match_status', ['completed', 'cancelled'])
            )
            ->exists();

        if ($hasPendingMatches) {
            throw new \DomainException(
                'Ainda existem partidas pendentes na fase atual. Encerre todas as partidas antes de avançar.'
            );
        }

        DB::transaction(function () use ($championship, $currentPhase) {
            if ($currentPhase->type === 'group_stage') {
                $this->finalizeGroupStage($currentPhase);
            }

            $nextPhase = $championship->phases()
                ->where('phase_order', '>', $currentPhase->phase_order)
                ->orderBy('phase_order')
                ->first();

            if ($nextPhase === null) {
                // Encerrar o campeonato
                $this->championshipService->finish($championship);
                return;
            }

            if ($nextPhase->type === 'knockout') {
                $seededTeams = $this->collectSeededTeams($currentPhase, $nextPhase);
                $this->bracketService->generate($nextPhase, $seededTeams);
            }
        });

        return $championship->refresh();
    }

    /**
     * Calcula e persiste a classificação final de cada grupo da fase.
     */
    private function finalizeGroupStage(ChampionshipPhase $phase): void
    {
        foreach ($phase->groups as $group) {
            $standing = $this->calculateGroupStanding($group);

            foreach ($standing as $position => $teamSportModeId) {
                ChampionshipGroupEntry::where('championship_group_id', $group->id)
                    ->where('team_sport_mode_id', $teamSportModeId)
                    ->update(['final_position' => $position + 1]);
            }
        }
    }

    /**
     * Retorna times do grupo ordenados por pts → DG → GF.
     * @return array<int>  team_sport_mode_id[] em ordem de classificação
     */
    private function calculateGroupStanding(ChampionshipGroup $group): array
    {
        $entries        = $group->entries()->with('teamSportMode')->get();
        $teamSportModes = $entries->pluck('team_sport_mode_id')->all();

        $stats = [];
        foreach ($teamSportModes as $id) {
            $stats[$id] = ['pts' => 0, 'gd' => 0, 'gf' => 0];
        }

        // Apenas partidas completed da fase
        $matches = ChampionshipMatch::query()
            ->whereHas('round', fn ($q) => $q->where('championship_phase_id', $group->championship_phase_id))
            ->where('match_status', 'completed')
            ->where(fn ($q) =>
                $q->whereIn('home_team_id', $teamSportModes)
                  ->orWhereIn('away_team_id', $teamSportModes)
            )
            ->get();

        foreach ($matches as $match) {
            $homeId = $match->home_team_id;
            $awayId = $match->away_team_id;
            $hg     = $match->home_goals ?? 0;
            $ag     = $match->away_goals ?? 0;

            if (!isset($stats[$homeId]) || !isset($stats[$awayId])) {
                continue; // partida de outro grupo
            }

            $stats[$homeId]['gf'] += $hg;
            $stats[$homeId]['gd'] += ($hg - $ag);
            $stats[$awayId]['gf'] += $ag;
            $stats[$awayId]['gd'] += ($ag - $hg);

            if ($hg > $ag) {
                $stats[$homeId]['pts'] += 3;
            } elseif ($hg === $ag) {
                $stats[$homeId]['pts'] += 1;
                $stats[$awayId]['pts'] += 1;
            } else {
                $stats[$awayId]['pts'] += 3;
            }
        }

        // Ordenar por pts DESC, gd DESC, gf DESC, team_id ASC (desempate final)
        uksort($stats, function ($a, $b) use ($stats) {
            if ($stats[$a]['pts'] !== $stats[$b]['pts']) {
                return $stats[$b]['pts'] <=> $stats[$a]['pts'];
            }
            if ($stats[$a]['gd'] !== $stats[$b]['gd']) {
                return $stats[$b]['gd'] <=> $stats[$a]['gd'];
            }
            if ($stats[$a]['gf'] !== $stats[$b]['gf']) {
                return $stats[$b]['gf'] <=> $stats[$a]['gf'];
            }
            return $a <=> $b;
        });

        return array_keys($stats);
    }

    /**
     * Coleta times classificados da fase atual, ordenados por semente para o bracket.
     * Interleave: 1º grupo A, 1º grupo B, 2º grupo A, 2º grupo B… (padrão Copa do Mundo)
     *
     * @return array<int>  team_sport_mode_id[]
     */
    private function collectSeededTeams(
        ChampionshipPhase $currentPhase,
        ChampionshipPhase $nextPhase
    ): array {
        $advancesCount = $currentPhase->advances_count;
        $groups        = $currentPhase->groups()->with('entries')->get();
        $seeded        = [];

        for ($pos = 1; $pos <= $advancesCount; $pos++) {
            foreach ($groups as $group) {
                $entry = $group->entries()
                    ->where('final_position', $pos)
                    ->first();

                if ($entry !== null) {
                    $seeded[] = $entry->team_sport_mode_id;
                }
            }
        }

        return $seeded;
    }

    private function resolveCurrentPhase(Championship $championship): ?ChampionshipPhase
    {
        // A fase atual é a de menor phase_order que ainda possui partidas pendentes.
        return $championship->phases()
            ->whereHas('rounds.matches', fn ($q) =>
                $q->whereNotIn('match_status', ['completed', 'cancelled'])
            )
            ->orderBy('phase_order')
            ->first();
    }
}
```

### 5.3 Atualizações em `ChampionshipService`

#### 5.3.1 Gate de plano ao criar

```php
public function create(array $data, User $actor): Championship
{
    if (in_array($data['format'], ['knockout', 'cup'], true)) {
        if (!$actor->isAdmin() && !$actor->isPlanAtLeast(UserPlan::Club)) {
            throw new \DomainException(
                'Os formatos knockout e cup requerem o plano Club ou superior.'
            );
        }
    }

    // Continua com a criação normal...
}
```

#### 5.3.2 Badges coletivos ao encerrar

```php
private function awardCollectiveBadges(Championship $championship): void
{
    $winnerTeamSportModeId = $this->resolveChampion($championship);

    if ($winnerTeamSportModeId === null) {
        return;
    }

    $playerIds = ChampionshipTeamPlayer::query()
        ->where('championship_id', $championship->id)
        ->where('team_sport_mode_id', $winnerTeamSportModeId)
        ->with('playerMembership')
        ->get()
        ->pluck('playerMembership.player_id')
        ->unique()
        ->filter();

    foreach ($playerIds as $playerId) {
        // unbeaten_champion: time campeão sem nenhuma derrota
        if ($this->isUnbeatenChampion($championship, $winnerTeamSportModeId)) {
            $this->badgeService->awardIfNotDuplicate($playerId, 'unbeaten_champion', $championship->id);
        }

        // clean_sweep: venceu todas as partidas da fase de grupos
        if ($this->isCleanSweep($championship, $winnerTeamSportModeId)) {
            $this->badgeService->awardIfNotDuplicate($playerId, 'clean_sweep', $championship->id);
        }
    }
}

private function resolveChampion(Championship $championship): ?int
{
    if ($championship->format === 'league') {
        // Campeão: time com final_position = 1 no único grupo
        return ChampionshipGroupEntry::query()
            ->where('final_position', 1)
            ->whereHas('group.phase', fn ($q) =>
                $q->where('championship_id', $championship->id)
            )
            ->value('team_sport_mode_id');
    }

    // knockout / cup: vencedor da partida final (última rodada)
    $lastRound = ChampionshipRound::query()
        ->whereHas('phase', fn ($q) =>
            $q->where('championship_id', $championship->id)
              ->where('type', 'knockout')
        )
        ->orderByDesc('round_number')
        ->first();

    if ($lastRound === null) {
        return null;
    }

    $finalMatch = $lastRound->matches()
        ->where('match_status', 'completed')
        ->first();

    if ($finalMatch === null) {
        return null;
    }

    return $finalMatch->home_goals > $finalMatch->away_goals
        ? $finalMatch->home_team_id
        : ($finalMatch->away_goals > $finalMatch->home_goals
            ? $finalMatch->away_team_id
            : ($finalMatch->home_penalties > $finalMatch->away_penalties
                ? $finalMatch->home_team_id
                : $finalMatch->away_team_id));
}

private function isUnbeatenChampion(Championship $championship, int $teamSportModeId): bool
{
    return !ChampionshipMatch::query()
        ->where('match_status', 'completed')
        ->whereHas('round.phase', fn ($q) =>
            $q->where('championship_id', $championship->id)
        )
        ->where(fn ($q) =>
            // derrota do time
            $q->where(fn ($q2) =>
                $q2->where('home_team_id', $teamSportModeId)
                   ->whereRaw('home_goals < away_goals')
            )
            ->orWhere(fn ($q2) =>
                $q2->where('away_team_id', $teamSportModeId)
                   ->whereRaw('away_goals < home_goals')
            )
        )
        ->exists();
}

private function isCleanSweep(Championship $championship, int $teamSportModeId): bool
{
    // All group_stage matches must be wins
    $groupMatches = ChampionshipMatch::query()
        ->where('match_status', 'completed')
        ->whereHas('round.phase', fn ($q) =>
            $q->where('championship_id', $championship->id)
              ->where('type', 'group_stage')
        )
        ->where(fn ($q) =>
            $q->where('home_team_id', $teamSportModeId)
              ->orWhere('away_team_id', $teamSportModeId)
        )
        ->get();

    if ($groupMatches->isEmpty()) {
        return false;
    }

    foreach ($groupMatches as $match) {
        $isHome    = $match->home_team_id === $teamSportModeId;
        $teamGoals = $isHome ? $match->home_goals : $match->away_goals;
        $oppGoals  = $isHome ? $match->away_goals : $match->home_goals;

        if ($teamGoals <= $oppGoals) {
            return false; // empate ou derrota
        }
    }

    return true;
}
```

---

## 6. Form Requests

### 6.1 `StoreChampionshipRequest` — adições para knockout/cup

```php
// Acrescentar às rules() existentes:
'format'                           => ['required', Rule::in(['league', 'knockout', 'cup'])],
'phases'                           => ['required_if:format,knockout,cup', 'array'],
'phases.*.name'                    => ['required', 'string', 'max:60'],
'phases.*.type'                    => ['required', Rule::in(['group_stage', 'knockout'])],
'phases.*.phase_order'             => ['required', 'integer', 'min:1'],
'phases.*.legs'                    => ['required', 'integer', Rule::in([1, 2])],
'phases.*.advances_count'          => ['nullable', 'integer', 'min:1'],
```

### 6.2 `AdvanceChampionshipPhaseRequest` (novo)

Localização: `app/Http/Requests/Api/AdvanceChampionshipPhaseRequest.php`

```php
class AdvanceChampionshipPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $championship = $this->route('championship');
        return $this->user()->can('advance', $championship);
    }

    public function rules(): array
    {
        return []; // Sem payload — a ação não requer dados de entrada
    }
}
```

---

## 7. Policy — `ChampionshipPolicy` (adições)

```php
/**
 * Verificado ao criar um campeonato knockout ou cup.
 * Chamado internamente em ChampionshipService::create().
 * A Policy aqui cobre o gate geral de "pode criar campeonato".
 */
public function create(User $user): bool
{
    return true; // Qualquer autenticado pode tentar; gating de plano é no service
}

/**
 * Somente o criador ou admin podem avançar a fase.
 */
public function advance(User $user, Championship $championship): bool
{
    if ($user->isAdmin()) {
        return true;
    }

    return $championship->created_by === $user->id;
}
```

---

## 8. Resources — atualizações

### 8.1 `ChampionshipResource` — campo `phases`

```php
// Acrescentar ao toArray():
'phases' => ChampionshipPhaseResource::collection($this->whenLoaded('phases')),
```

### 8.2 `ChampionshipPhaseResource` (novo)

Localização: `app/Http/Resources/ChampionshipPhaseResource.php`

```php
class ChampionshipPhaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'type'            => $this->type,
            'phase_order'     => $this->phase_order,
            'legs'            => $this->legs,
            'advances_count'  => $this->advances_count,
            'groups'          => ChampionshipGroupResource::collection(
                $this->whenLoaded('groups')
            ),
        ];
    }
}
```

### 8.3 `ChampionshipGroupResource` (novo)

Localização: `app/Http/Resources/ChampionshipGroupResource.php`

```php
class ChampionshipGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'entries' => $this->whenLoaded('entries', fn () =>
                $this->entries->map(fn ($entry) => [
                    'team_sport_mode_id' => $entry->team_sport_mode_id,
                    'final_position'     => $entry->final_position,
                ])
            ),
        ];
    }
}
```

---

## 9. Controller — adição

### 9.1 `ChampionshipController::advancePhase()` (novo método)

```php
/**
 * POST /api/v1/championships/{championship}/advance-phase
 *
 * Avança o campeonato para a próxima fase ou encerra se for a última.
 */
public function advancePhase(
    AdvanceChampionshipPhaseRequest $request,
    Championship $championship
): JsonResponse {
    $championship = $this->advancementService->advance($championship, $request->user());

    return $this->sendResponse(
        new ChampionshipResource($championship),
        'Championship phase advanced successfully.'
    );
}
```

O `ChampionshipController` recebe `ChampionshipPhaseAdvancementService` via injeção de dependência no construtor.

---

## 10. Rotas

Adicionar em `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('v1/championships')->group(function () {
        // ... rotas existentes da Fase 3

        Route::post('{championship}/advance-phase', [ChampionshipController::class, 'advancePhase']);
    });
});
```

---

## 11. Types TypeScript

Localização: `resources/js/types/championship.ts` (complemento)

```typescript
export type ChampionshipFormat = 'league' | 'knockout' | 'cup';
export type ChampionshipPhaseType = 'group_stage' | 'knockout';

export interface ChampionshipPhase {
    id: number;
    name: string;
    type: ChampionshipPhaseType;
    phase_order: number;
    legs: 1 | 2;
    advances_count: number | null;
    groups?: ChampionshipGroup[];
}

export interface ChampionshipGroup {
    id: number;
    name: string;
    entries?: ChampionshipGroupEntry[];
}

export interface ChampionshipGroupEntry {
    team_sport_mode_id: number;
    final_position: number | null;
}

export interface BracketMatch {
    id: number;
    round_number: number;
    round_name: string;
    home_team_id: number | null;
    away_team_id: number | null;
    home_goals: number | null;
    away_goals: number | null;
    home_penalties: number | null;
    away_penalties: number | null;
    leg: 1 | 2;
    match_status: 'scheduled' | 'completed' | 'cancelled' | 'postponed';
}
```

---

## 12. Factories

### 12.1 `ChampionshipPhaseFactory`

Localização: `database/factories/ChampionshipPhaseFactory.php`

```php
class ChampionshipPhaseFactory extends Factory
{
    protected $model = ChampionshipPhase::class;

    public function definition(): array
    {
        return [
            'championship_id' => Championship::factory(),
            'name'            => fake()->randomElement(['Fase de Grupos', 'Quartas de Final', 'Semifinal', 'Final']),
            'type'            => 'group_stage',
            'phase_order'     => 1,
            'legs'            => 1,
            'advances_count'  => 2,
        ];
    }

    public function knockout(): static
    {
        return $this->state(['type' => 'knockout', 'advances_count' => null]);
    }

    public function twoLegs(): static
    {
        return $this->state(['legs' => 2]);
    }
}
```

### 12.2 `ChampionshipGroupFactory`

Localização: `database/factories/ChampionshipGroupFactory.php`

```php
class ChampionshipGroupFactory extends Factory
{
    protected $model = ChampionshipGroup::class;

    public function definition(): array
    {
        return [
            'championship_phase_id' => ChampionshipPhase::factory(),
            'name'                  => fake()->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
```

### 12.3 `ChampionshipGroupEntryFactory`

Localização: `database/factories/ChampionshipGroupEntryFactory.php`

```php
class ChampionshipGroupEntryFactory extends Factory
{
    protected $model = ChampionshipGroupEntry::class;

    public function definition(): array
    {
        return [
            'championship_group_id' => ChampionshipGroup::factory(),
            'team_sport_mode_id'    => TeamSportMode::factory(),
            'final_position'        => null,
        ];
    }

    public function classified(int $position = 1): static
    {
        return $this->state(['final_position' => $position]);
    }
}
```

---

## 13. Testes

### 13.1 `ChampionshipKnockoutCreationTest`

Localização: `tests/Feature/Api/ChampionshipKnockoutCreationTest.php`

```php
class ChampionshipKnockoutCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_club_user_can_create_knockout_championship(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Club]);

        $response = $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'       => 'Copa Verão',
            'format'     => 'knockout',
            'starts_at'  => '2025-01-10',
            'ends_at'    => '2025-02-10',
            'phases'     => [[
                'name'           => 'Mata-mata',
                'type'           => 'knockout',
                'phase_order'    => 1,
                'legs'           => 1,
                'advances_count' => null,
            ]],
        ]);

        $response->assertCreated()
                 ->assertJsonPath('success', true);
    }

    public function test_free_user_cannot_create_knockout_championship(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'    => 'Copa',
            'format'  => 'knockout',
            'phases'  => [],
        ])->assertStatus(422); // DomainException mapeada para 422
    }

    public function test_free_user_can_create_league_championship(): void
    {
        $user = User::factory()->create(['plan' => UserPlan::Free]);

        $response = $this->actingAs($user)->postJson('/api/v1/championships', [
            'name'      => 'Pelada Clássica',
            'format'    => 'league',
            'starts_at' => '2025-01-10',
            'ends_at'   => '2025-03-10',
            'phases'    => [[
                'name'        => 'Fase Única',
                'type'        => 'group_stage',
                'phase_order' => 1,
                'legs'        => 1,
                'advances_count' => null,
            ]],
        ]);

        $response->assertCreated();
    }
}
```

### 13.2 `ChampionshipBracketTest`

Localização: `tests/Feature/Api/ChampionshipBracketTest.php`

```php
class ChampionshipBracketTest extends TestCase
{
    use RefreshDatabase;

    public function test_bracket_generates_correct_number_of_matches_for_8_teams(): void
    {
        $phase = ChampionshipPhase::factory()->knockout()->create();
        $teams = TeamSportMode::factory()->count(8)->create()->pluck('id')->all();

        app(ChampionshipBracketService::class)->generate($phase, $teams);

        // 8 times → quartas (4), semis (2), final (1) = 7 partidas
        $this->assertEquals(7, ChampionshipMatch::whereHas('round', fn ($q) =>
            $q->where('championship_phase_id', $phase->id)
        )->count());
    }

    public function test_bracket_pads_non_power_of_two_teams_with_byes(): void
    {
        $phase = ChampionshipPhase::factory()->knockout()->create();
        $teams = TeamSportMode::factory()->count(6)->create()->pluck('id')->all();

        app(ChampionshipBracketService::class)->generate($phase, $teams);

        // 6 times → potência de 2 = 8 → 2 byes na 1ª rodada → 4+2+1 = 7 partidas
        $byeMatches = ChampionshipMatch::whereHas('round', fn ($q) =>
            $q->where('championship_phase_id', $phase->id)->where('round_number', 1)
        )->where(fn ($q) =>
            $q->whereNull('home_team_id')->orWhereNull('away_team_id')
        )->count();

        $this->assertEquals(2, $byeMatches);
    }

    public function test_first_round_seeds_follow_fifa_pattern(): void
    {
        $phase = ChampionshipPhase::factory()->knockout()->create();
        $teams = [101, 102, 103, 104]; // seeds 1–4

        app(ChampionshipBracketService::class)->generate($phase, $teams);

        $firstRound = ChampionshipRound::where('championship_phase_id', $phase->id)
            ->where('round_number', 1)
            ->first();

        $matches = ChampionshipMatch::where('championship_round_id', $firstRound->id)
            ->orderBy('id')
            ->get();

        // Seed 1 vs seed 4; seed 2 vs seed 3
        $this->assertEquals(101, $matches[0]->home_team_id);
        $this->assertEquals(104, $matches[0]->away_team_id);
        $this->assertEquals(102, $matches[1]->home_team_id);
        $this->assertEquals(103, $matches[1]->away_team_id);
    }
}
```

### 13.3 `ChampionshipPhaseAdvancementTest`

Localização: `tests/Feature/Api/ChampionshipPhaseAdvancementTest.php`

```php
class ChampionshipPhaseAdvancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_advance_phase(): void
    {
        $user         = User::factory()->create(['plan' => UserPlan::Club]);
        $championship = Championship::factory()->create([
            'created_by' => $user->id,
            'format'     => 'cup',
            'status'     => 'active',
        ]);

        // Setup: fase de grupos com todas as partidas concluídas
        $phase  = ChampionshipPhase::factory()->create([
            'championship_id' => $championship->id,
            'type'            => 'group_stage',
            'phase_order'     => 1,
            'advances_count'  => 2,
        ]);
        // ... criar grupos, partidas e resultados via factories

        $this->actingAs($user)
             ->postJson("/api/v1/championships/{$championship->id}/advance-phase")
             ->assertOk()
             ->assertJsonPath('success', true);
    }

    public function test_advance_fails_if_pending_matches_exist(): void
    {
        $user         = User::factory()->create(['plan' => UserPlan::Club]);
        $championship = Championship::factory()->create([
            'created_by' => $user->id,
            'format'     => 'cup',
            'status'     => 'active',
        ]);

        $phase = ChampionshipPhase::factory()->create([
            'championship_id' => $championship->id,
            'type'            => 'group_stage',
            'phase_order'     => 1,
        ]);
        $round = ChampionshipRound::factory()->create(['championship_phase_id' => $phase->id]);
        ChampionshipMatch::factory()->create([
            'championship_round_id' => $round->id,
            'match_status'          => 'scheduled', // pendente
        ]);

        $this->actingAs($user)
             ->postJson("/api/v1/championships/{$championship->id}/advance-phase")
             ->assertStatus(422);
    }

    public function test_non_creator_cannot_advance_phase(): void
    {
        $other        = User::factory()->create(['plan' => UserPlan::Club]);
        $championship = Championship::factory()->create(['format' => 'cup', 'status' => 'active']);

        $this->actingAs($other)
             ->postJson("/api/v1/championships/{$championship->id}/advance-phase")
             ->assertForbidden();
    }
}
```

### 13.4 `ChampionshipCollectiveBadgeTest`

Localização: `tests/Feature/Api/ChampionshipCollectiveBadgeTest.php`

```php
class ChampionshipCollectiveBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unbeaten_champion_badge_is_awarded_to_all_players_of_winner(): void
    {
        // Setup: badgeType unbeaten_champion no banco
        $badgeType = BadgeType::factory()->create(['name' => 'unbeaten_champion', 'scope' => 'championship']);

        $championship   = Championship::factory()->create(['format' => 'league', 'status' => 'active']);
        $winnerTsm      = TeamSportMode::factory()->create();
        $player1        = Player::factory()->create();
        $player2        = Player::factory()->create();
        $membership1    = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $winnerTsm->id, 'player_id' => $player1->user_id,
        ]);
        $membership2    = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $winnerTsm->id, 'player_id' => $player2->user_id,
        ]);

        // Registrar jogadores na championship_team_players
        ChampionshipTeamPlayer::factory()->create([
            'championship_id'       => $championship->id,
            'team_sport_mode_id'    => $winnerTsm->id,
            'player_membership_id'  => $membership1->id,
        ]);
        ChampionshipTeamPlayer::factory()->create([
            'championship_id'       => $championship->id,
            'team_sport_mode_id'    => $winnerTsm->id,
            'player_membership_id'  => $membership2->id,
        ]);

        // Time campeão: sem derrotas (nenhuma partida adversa completada)
        app(ChampionshipService::class)->finish($championship);

        $this->assertDatabaseHas('player_badges', [
            'player_id'        => $player1->user_id,
            'championship_id'  => $championship->id,
        ]);
        $this->assertDatabaseHas('player_badges', [
            'player_id'        => $player2->user_id,
            'championship_id'  => $championship->id,
        ]);
    }

    public function test_clean_sweep_is_not_awarded_if_team_drew_in_groups(): void
    {
        $badgeType    = BadgeType::factory()->create(['name' => 'clean_sweep', 'scope' => 'championship']);
        $championship = Championship::factory()->create(['format' => 'cup', 'status' => 'active']);
        $winnerTsm    = TeamSportMode::factory()->create();
        $opponentTsm  = TeamSportMode::factory()->create();
        $player       = Player::factory()->create();
        $membership   = PlayerMembership::factory()->create([
            'team_sport_mode_id' => $winnerTsm->id, 'player_id' => $player->user_id,
        ]);
        ChampionshipTeamPlayer::factory()->create([
            'championship_id'       => $championship->id,
            'team_sport_mode_id'    => $winnerTsm->id,
            'player_membership_id'  => $membership->id,
        ]);

        // Criar partida de grupos empatada
        $phase = ChampionshipPhase::factory()->create([
            'championship_id' => $championship->id, 'type' => 'group_stage',
        ]);
        $round = ChampionshipRound::factory()->create(['championship_phase_id' => $phase->id]);
        ChampionshipMatch::factory()->create([
            'championship_round_id' => $round->id,
            'home_team_id'          => $winnerTsm->id,
            'away_team_id'          => $opponentTsm->id,
            'home_goals'            => 1,
            'away_goals'            => 1,
            'match_status'          => 'completed',
        ]);

        app(ChampionshipService::class)->finish($championship);

        $this->assertDatabaseMissing('player_badges', [
            'player_id'      => $player->user_id,
            'badge_type_id'  => $badgeType->id,
        ]);
    }
}
```

---

## 14. Diagrama de Componentes

```
ChampionshipController (API)
  ├── POST /api/v1/championships                   → ChampionshipService::create() [gate Club+ para knockout/cup]
  └── POST /api/v1/championships/{id}/advance-phase → ChampionshipPhaseAdvancementService::advance()
           ├── finalizeGroupStage()                 → calcula final_position por grupo
           ├── collectSeededTeams()                 → classifica times por advances_count
           └── ChampionshipBracketService::generate()
                └── cria championship_rounds + championship_matches
                     (1ª rodada: pareados por semente; rodadas seguintes: null placeholders)

ChampionshipService::finish()
  ├── ... awards e badges individuais (Fase 3)
  ├── resolveChampion()                             → identifica time campeão
  └── awardCollectiveBadges()
       ├── isUnbeatenChampion()                     → zero derrotas no campeonato
       ├── isCleanSweep()                            → 100% vitórias na fase de grupos
       └── BadgeService::awardIfNotDuplicate()       → player_badges para todos do time campeão
```
