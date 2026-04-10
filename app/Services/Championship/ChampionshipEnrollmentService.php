<?php

namespace App\Services\Championship;

use App\Enums\ChampionshipStatus;
use App\Models\Championship;
use App\Models\ChampionshipTeam;
use App\Models\ChampionshipTeamPlayer;
use App\Models\PlayerMembership;
use App\Models\TeamSportMode;
use DomainException;

class ChampionshipEnrollmentService
{
    public function enroll(Championship $championship, TeamSportMode $teamSportMode): ChampionshipTeam
    {
        if (! $championship->isEnrollment()) {
            throw new DomainException('Inscrições aceitas somente durante o período de enrollment.');
        }

        if (! $championship->sportModes()->where('sport_modes.id', $teamSportMode->sport_mode_id)->exists()) {
            throw new DomainException('A modalidade do time não é suportada por este campeonato.');
        }

        if ($championship->teams()->where('team_sport_mode_id', $teamSportMode->id)->exists()) {
            throw new DomainException('Este time já está inscrito no campeonato.');
        }

        return $championship->teams()->create([
            'team_sport_mode_id' => $teamSportMode->id,
        ]);
    }

    public function removeTeam(Championship $championship, TeamSportMode $teamSportMode): void
    {
        if (! $championship->isEnrollment()) {
            throw new DomainException('Times só podem ser removidos durante o período de enrollment.');
        }

        $championship->teams()->where('team_sport_mode_id', $teamSportMode->id)->delete();

        ChampionshipTeamPlayer::query()
            ->where('championship_id', $championship->id)
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->delete();
    }

    /**
     * @param  array<int, int>  $membershipIds
     */
    public function selectPlayers(Championship $championship, TeamSportMode $teamSportMode, array $membershipIds): void
    {
        if ($championship->isActive()
            || $championship->status === ChampionshipStatus::Finished
            || $championship->status === ChampionshipStatus::Archived) {
            throw new DomainException('A seleção de jogadores só é permitida durante o período de inscrições (enrollment). Após o início do campeonato, a lista é bloqueada.');
        }

        if (! $championship->teams()->where('team_sport_mode_id', $teamSportMode->id)->exists()) {
            throw new DomainException('O time precisa estar inscrito no campeonato antes de selecionar jogadores.');
        }

        if (count($membershipIds) > $championship->max_players) {
            throw new DomainException("O campeonato permite no máximo {$championship->max_players} jogadores por time.");
        }

        $validMembershipCount = PlayerMembership::query()
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->whereIn('id', $membershipIds)
            ->count();

        if ($validMembershipCount !== count($membershipIds)) {
            throw new DomainException('Todos os jogadores selecionados devem pertencer ao elenco ativo da modalidade inscrita.');
        }

        ChampionshipTeamPlayer::query()
            ->where('championship_id', $championship->id)
            ->where('team_sport_mode_id', $teamSportMode->id)
            ->delete();

        foreach ($membershipIds as $membershipId) {
            ChampionshipTeamPlayer::create([
                'championship_id' => $championship->id,
                'team_sport_mode_id' => $teamSportMode->id,
                'player_membership_id' => $membershipId,
            ]);
        }
    }
}
