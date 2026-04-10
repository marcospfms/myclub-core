<?php

namespace App\Services\Championship;

use App\Enums\MatchStatus;
use App\Models\Championship;
use App\Models\ChampionshipMatch;
use App\Models\ChampionshipMatchHighlight;
use App\Models\ChampionshipTeamPlayer;
use DomainException;

class ChampionshipMatchService
{
    public function __construct(
        private readonly ChampionshipClosingService $closingService,
    ) {}

    public function registerResult(ChampionshipMatch $match, array $data): ChampionshipMatch
    {
        if ($match->isCompleted()) {
            throw new DomainException('Resultado já registrado. Edição não permitida após completed.');
        }

        if ($match->match_status === MatchStatus::Cancelled) {
            throw new DomainException('Não é possível registrar resultado em partida cancelada.');
        }

        $match->update(array_merge($data, [
            'match_status' => MatchStatus::Completed,
        ]));

        $this->maybeFinish($match->round->phase->championship);

        return $match->fresh();
    }

    public function cancelMatch(ChampionshipMatch $match): ChampionshipMatch
    {
        if ($match->isCompleted()) {
            throw new DomainException('Não é possível cancelar uma partida já encerrada.');
        }

        $match->update([
            'match_status' => MatchStatus::Cancelled,
        ]);

        $this->maybeFinish($match->round->phase->championship);

        return $match->fresh();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function registerHighlights(ChampionshipMatch $match, array $items): void
    {
        if (! $match->isCompleted()) {
            throw new DomainException('Estatísticas só podem ser registradas em partidas encerradas.');
        }

        $allowedMembershipIds = ChampionshipTeamPlayer::query()
            ->where('championship_id', $match->round->phase->championship_id)
            ->whereIn('team_sport_mode_id', [$match->home_team_id, $match->away_team_id])
            ->pluck('player_membership_id')
            ->all();

        foreach ($items as $item) {
            if (! in_array($item['player_membership_id'], $allowedMembershipIds, true)) {
                throw new DomainException(
                    "player_membership_id {$item['player_membership_id']} não pertence aos atletas inscritos dos times desta partida."
                );
            }

            if (($item['is_mvp'] ?? false) === true) {
                $match->highlights()->where('is_mvp', true)->update(['is_mvp' => false]);
            }

            ChampionshipMatchHighlight::updateOrCreate(
                [
                    'championship_match_id' => $match->id,
                    'player_membership_id' => $item['player_membership_id'],
                ],
                [
                    'goals' => $item['goals'] ?? 0,
                    'assists' => $item['assists'] ?? 0,
                    'yellow_cards' => $item['yellow_cards'] ?? 0,
                    'red_cards' => $item['red_cards'] ?? 0,
                    'is_mvp' => $item['is_mvp'] ?? false,
                ],
            );
        }
    }

    private function maybeFinish(Championship $championship): void
    {
        if (! $championship->isActive()) {
            return;
        }

        $pendingMatches = ChampionshipMatch::query()
            ->whereHas('round.phase', fn ($query) => $query->where('championship_id', $championship->id))
            ->whereNotIn('match_status', [MatchStatus::Completed->value, MatchStatus::Cancelled->value])
            ->exists();

        if (! $pendingMatches) {
            $this->closingService->finish($championship);
        }
    }
}
