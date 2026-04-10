<?php

namespace App\Services\FriendlyMatch;

use App\Models\User;
use DomainException;
use App\Models\FriendlyMatch;
use App\Models\TeamSportMode;
use App\Enums\MatchStatus;
use App\Enums\ResultStatus;
use App\Enums\MatchConfirmation;
use App\Notifications\FriendlyMatch\FriendlyMatchInvitedNotification;
use App\Notifications\FriendlyMatch\FriendlyMatchRejectedNotification;
use App\Notifications\FriendlyMatch\FriendlyMatchConfirmedNotification;

class FriendlyMatchService
{
    public function create(array $data, User $challenger): FriendlyMatch
    {
        $homeTeam = TeamSportMode::query()->with('team')->findOrFail($data['home_team_id']);
        $awayTeam = TeamSportMode::query()->with('team')->findOrFail($data['away_team_id']);

        if ($homeTeam->team->owner_id !== $challenger->id && ! $challenger->isAdmin()) {
            throw new DomainException('Apenas o dono do time desafiante pode criar o amistoso.');
        }

        if ($homeTeam->sport_mode_id !== $awayTeam->sport_mode_id) {
            throw new DomainException('Os times devem competir na mesma modalidade esportiva.');
        }

        if ($homeTeam->team_id === $awayTeam->team_id) {
            throw new DomainException('Um time não pode desafiar a si mesmo.');
        }

        $match = FriendlyMatch::create(array_merge($data, [
            'confirmation' => MatchConfirmation::Pending,
            'match_status' => null,
            'result_status' => ResultStatus::None,
            'invite_expires_at' => now()->addDays(2),
        ]));

        $match->loadMissing(['awayTeam.team.owner']);
        $match->awayTeam->team->owner->notify(new FriendlyMatchInvitedNotification($match));

        return $match;
    }

    public function confirm(FriendlyMatch $match): FriendlyMatch
    {
        if (! $match->isPending()) {
            throw new DomainException('Amistoso não está pendente de confirmação.');
        }

        $match->update([
            'confirmation' => MatchConfirmation::Confirmed,
            'match_status' => MatchStatus::Scheduled,
        ]);

        $updated = $match->fresh(['homeTeam.team.owner']);

        $updated->homeTeam->team->owner->notify(new FriendlyMatchConfirmedNotification($updated));

        return $updated;
    }

    public function reject(FriendlyMatch $match): FriendlyMatch
    {
        if (! $match->isPending()) {
            throw new DomainException('Amistoso não está pendente de confirmação.');
        }

        $match->update([
            'confirmation' => MatchConfirmation::Rejected,
            'match_status' => null,
        ]);

        $updated = $match->fresh(['homeTeam.team.owner']);

        $updated->homeTeam->team->owner->notify(new FriendlyMatchRejectedNotification($updated));

        return $updated;
    }

    public function cancel(FriendlyMatch $match): FriendlyMatch
    {
        if (! $match->isConfirmed()) {
            throw new DomainException('Apenas amistosos confirmados podem ser cancelados.');
        }

        if ($match->isCompleted()) {
            throw new DomainException('Não é possível cancelar um amistoso já encerrado.');
        }

        $match->update([
            'match_status' => MatchStatus::Cancelled,
        ]);

        return $match->fresh();
    }

    public function postpone(FriendlyMatch $match, array $data): FriendlyMatch
    {
        if (! $match->isConfirmed()) {
            throw new DomainException('Apenas amistosos confirmados podem ser adiados.');
        }

        if ($match->isCompleted()) {
            throw new DomainException('Não é possível adiar um amistoso já encerrado.');
        }

        $match->update([
            'match_status' => MatchStatus::Postponed,
            'scheduled_at' => $data['scheduled_at'] ?? $match->scheduled_at,
            'location' => $data['location'] ?? $match->location,
        ]);

        return $match->fresh();
    }

    public function removePendingInvite(FriendlyMatch $match): void
    {
        if (! $match->isPending()) {
            throw new DomainException('Apenas convites pendentes podem ser removidos.');
        }

        $match->delete();
    }
}
