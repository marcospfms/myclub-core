<?php

namespace App\Services\FriendlyMatch;

use App\Enums\MatchStatus;
use App\Enums\ResultStatus;
use App\Models\FriendlyMatch;
use App\Models\PerformanceHighlight;
use App\Models\PlayerMembership;
use App\Models\User;
use App\Notifications\FriendlyMatch\MatchResultConfirmedNotification;
use App\Notifications\FriendlyMatch\MatchResultRegisteredNotification;
use DomainException;

class MatchResultService
{
    public function register(FriendlyMatch $match, array $data, User $registeredBy): FriendlyMatch
    {
        if ($match->match_status !== MatchStatus::Scheduled && $match->match_status !== MatchStatus::Postponed) {
            throw new DomainException('Resultado só pode ser registrado em amistosos confirmados.');
        }

        if ($match->isCompleted()) {
            throw new DomainException('Resultado já confirmado. Edição não permitida.');
        }

        $payload = [
            'home_goals' => $data['home_goals'],
            'away_goals' => $data['away_goals'],
            'result_status' => ResultStatus::Pending,
            'result_registered_by' => $registeredBy->id,
        ];

        if ($registeredBy->isAdmin()) {
            $payload['home_notes'] = $data['home_notes'] ?? $match->home_notes;
            $payload['away_notes'] = $data['away_notes'] ?? $match->away_notes;
        } elseif ($registeredBy->id === $match->homeTeam->team->owner_id) {
            $payload['home_notes'] = $data['home_notes'] ?? $match->home_notes;
        } elseif ($registeredBy->id === $match->awayTeam->team->owner_id) {
            $payload['away_notes'] = $data['away_notes'] ?? $match->away_notes;
        }

        $match->update($payload);

        $updated = $match->fresh(['homeTeam.team.owner', 'awayTeam.team.owner']);

        $this->resolveOtherOwner($updated, $registeredBy)
            ->notify(new MatchResultRegisteredNotification($updated));

        return app(FriendlyMatchService::class)->loadForApi($updated);
    }

    public function confirmResult(FriendlyMatch $match): FriendlyMatch
    {
        if ($match->result_status !== ResultStatus::Pending) {
            throw new DomainException('Resultado não está aguardando confirmação.');
        }

        $match->update([
            'result_status' => ResultStatus::Confirmed,
            'match_status' => MatchStatus::Completed,
        ]);

        $updated = $match->fresh(['resultRegisteredBy']);

        if ($updated->resultRegisteredBy) {
            $updated->resultRegisteredBy->notify(new MatchResultConfirmedNotification($updated));
        }

        return app(FriendlyMatchService::class)->loadForApi($updated);
    }

    public function disputeResult(FriendlyMatch $match): FriendlyMatch
    {
        if ($match->result_status !== ResultStatus::Pending) {
            throw new DomainException('Resultado não está aguardando confirmação.');
        }

        $match->update([
            'result_status' => ResultStatus::Disputed,
            'home_goals' => null,
            'away_goals' => null,
            'result_registered_by' => null,
        ]);

        return app(FriendlyMatchService::class)->loadForApi($match->fresh());
    }

    public function registerHighlight(FriendlyMatch $match, array $item): PerformanceHighlight
    {
        if (! $match->isCompleted()) {
            throw new DomainException('Estatísticas só podem ser registradas após o encerramento do amistoso.');
        }

        $membership = PlayerMembership::query()->findOrFail($item['player_membership_id']);

        if (! in_array($membership->team_sport_mode_id, [$match->home_team_id, $match->away_team_id], true)) {
            throw new DomainException('O jogador informado não pertence a nenhum dos times do amistoso.');
        }

        return PerformanceHighlight::updateOrCreate(
            [
                'friendly_match_id' => $match->id,
                'player_membership_id' => $item['player_membership_id'],
            ],
            [
                'goals' => $item['goals'] ?? 0,
                'assists' => $item['assists'] ?? 0,
                'yellow_cards' => $item['yellow_cards'] ?? 0,
                'red_cards' => $item['red_cards'] ?? 0,
            ],
        )->loadMissing([
            'playerMembership.player.user',
            'playerMembership.position',
        ]);
    }

    private function resolveOtherOwner(FriendlyMatch $match, User $current): User
    {
        $match->loadMissing(['homeTeam.team.owner', 'awayTeam.team.owner']);

        return $current->id === $match->homeTeam->team->owner_id
            ? $match->awayTeam->team->owner
            : $match->homeTeam->team->owner;
    }
}
