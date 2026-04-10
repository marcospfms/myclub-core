<?php

namespace App\Services\Championship;

use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use App\Enums\MatchStatus;
use App\Enums\PhaseType;
use App\Models\Championship;
use App\Models\ChampionshipPhase;
use App\Models\ChampionshipTeam;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ChampionshipService
{
    public function loadForApi(Championship $championship): Championship
    {
        return $championship->load([
            'creator',
            'category',
            'sportModes',
            'phases.groups.entries.teamSportMode.team',
            'phases.rounds.matches.homeTeam.team',
            'phases.rounds.matches.awayTeam.team',
            'teams.teamSportMode.team',
            'awards.player.user',
            'playerBadges.badgeType',
        ]);
    }

    public function create(array $data, User $creator): Championship
    {
        if (! $creator->isAdmin()) {
            $plan = $creator->plan ?? 'free';

            if (in_array($plan, ['free', 'player_pro'], true)) {
                $activeCount = Championship::query()
                    ->where('created_by', $creator->id)
                    ->where('format', ChampionshipFormat::League->value)
                    ->whereNotIn('status', [
                        ChampionshipStatus::Finished->value,
                        ChampionshipStatus::Archived->value,
                        ChampionshipStatus::Cancelled->value,
                    ])
                    ->count();

                if ($activeCount >= 1) {
                    throw new DomainException(
                        'Plano Free permite apenas 1 campeonato league ativo por vez. Encerre o campeonato atual ou faça upgrade para o plano Club.'
                    );
                }
            }
        }

        return DB::transaction(function () use ($data, $creator): Championship {
            $sportModeIds = $data['sport_mode_ids'] ?? [];
            unset($data['sport_mode_ids']);

            $championship = Championship::create(array_merge($data, [
                'created_by' => $creator->id,
                'status' => ChampionshipStatus::Draft,
            ]));

            if ($sportModeIds !== []) {
                $championship->sportModes()->sync($sportModeIds);
            }

            return $this->loadForApi($championship);
        });
    }

    public function update(Championship $championship, array $data): Championship
    {
        if (! $championship->isDraft()) {
            throw new DomainException('Apenas campeonatos em rascunho podem ser editados livremente.');
        }

        return DB::transaction(function () use ($championship, $data): Championship {
            $sportModeIds = $data['sport_mode_ids'] ?? null;
            unset($data['sport_mode_ids']);

            $championship->update($data);

            if ($sportModeIds !== null) {
                $championship->sportModes()->sync($sportModeIds);
            }

            return $this->loadForApi($championship->fresh());
        });
    }

    public function openEnrollment(Championship $championship): Championship
    {
        if (! $championship->isDraft()) {
            throw new DomainException('Apenas campeonatos em rascunho podem abrir inscrições.');
        }

        if ($championship->sportModes()->doesntExist()) {
            throw new DomainException('Configure ao menos uma modalidade antes de abrir inscrições.');
        }

        $championship->update(['status' => ChampionshipStatus::Enrollment]);

        return $this->loadForApi($championship->fresh());
    }

    public function activate(Championship $championship): Championship
    {
        if (! $championship->isEnrollment()) {
            throw new DomainException('Apenas campeonatos em inscrição podem ser ativados.');
        }

        $teams = $championship->teams()->orderBy('team_sport_mode_id')->get();

        if ($teams->count() < 3) {
            throw new DomainException('São necessários ao menos 3 times inscritos para iniciar o campeonato.');
        }

        return DB::transaction(function () use ($championship, $teams): Championship {
            $championship->update(['status' => ChampionshipStatus::Active]);

            $phase = $championship->phases()->create([
                'name' => 'Fase Principal',
                'type' => PhaseType::GroupStage,
                'phase_order' => 1,
                'legs' => 1,
                'advances_count' => 0,
            ]);

            $group = $phase->groups()->create(['name' => 'Geral']);

            foreach ($teams as $entry) {
                $group->entries()->create([
                    'team_sport_mode_id' => $entry->team_sport_mode_id,
                ]);
            }

            $this->generateLeagueRounds($phase, $teams->pluck('team_sport_mode_id')->all());

            return $this->loadForApi($championship->fresh());
        });
    }

    public function cancel(Championship $championship, User $actor): Championship
    {
        if ($championship->isFinished() || $championship->status === ChampionshipStatus::Archived) {
            throw new DomainException('Campeonatos encerrados ou arquivados não podem ser cancelados.');
        }

        if ($championship->isActive() && ! $actor->isAdmin()) {
            throw new DomainException('Apenas admin pode cancelar um campeonato em andamento.');
        }

        $championship->update(['status' => ChampionshipStatus::Cancelled]);

        return $this->loadForApi($championship->fresh());
    }

    /**
     * @param  array<int, int>  $teamIds
     */
    private function generateLeagueRounds(ChampionshipPhase $phase, array $teamIds): void
    {
        if (count($teamIds) < 2) {
            return;
        }

        $rotation = $teamIds;

        if (count($rotation) % 2 !== 0) {
            $rotation[] = null;
        }

        $teamCount = count($rotation);
        $roundCount = $teamCount - 1;
        $matchesPerRound = intdiv($teamCount, 2);

        for ($roundNumber = 1; $roundNumber <= $roundCount; $roundNumber++) {
            $round = $phase->rounds()->create([
                'name' => "Rodada {$roundNumber}",
                'round_number' => $roundNumber,
            ]);

            for ($index = 0; $index < $matchesPerRound; $index++) {
                $homeTeamId = $rotation[$index];
                $awayTeamId = $rotation[$teamCount - 1 - $index];

                if ($homeTeamId === null || $awayTeamId === null) {
                    continue;
                }

                $round->matches()->create([
                    'home_team_id' => $homeTeamId,
                    'away_team_id' => $awayTeamId,
                    'match_status' => MatchStatus::Scheduled,
                    'leg' => 1,
                ]);
            }

            $fixed = array_shift($rotation);
            $last = array_pop($rotation);
            array_unshift($rotation, $fixed, $last);
        }
    }
}
