<?php

namespace App\Services\Team;

use App\Models\Team;
use App\Models\User;
use App\Models\TeamSportMode;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function listOwnedByUser(User $user): Collection
    {
        return Team::query()
            ->where('owner_id', $user->id)
            ->with(['owner', 'sportModes.sportMode', 'sportModes.activeMemberships'])
            ->get();
    }

    public function loadForApi(Team $team): Team
    {
        return $team->load(['owner', 'sportModes.sportMode', 'sportModes.activeMemberships']);
    }

    public function create(array $data, User $owner): Team
    {
        return DB::transaction(function () use ($data, $owner): Team {
            $sportModeIds = $data['sport_mode_ids'] ?? [];
            unset($data['sport_mode_ids']);

            $team = Team::create(array_merge($data, ['owner_id' => $owner->id]));

            foreach ($sportModeIds as $sportModeId) {
                $team->sportModes()->create(['sport_mode_id' => $sportModeId]);
            }

            return $this->loadForApi($team);
        });
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);

        return $this->loadForApi($team->fresh());
    }

    public function deactivate(Team $team): void
    {
        $team->update(['is_active' => false]);
    }

    public function addSportMode(Team $team, int $sportModeId): TeamSportMode
    {
        return $team->sportModes()->firstOrCreate(['sport_mode_id' => $sportModeId]);
    }

    public function removeSportMode(TeamSportMode $teamSportMode): void
    {
        if ($teamSportMode->activeMemberships()->exists()) {
            throw new DomainException('Não é possível remover modalidade com jogadores ativos.');
        }

        $teamSportMode->delete();
    }
}
