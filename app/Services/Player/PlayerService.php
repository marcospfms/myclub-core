<?php

namespace App\Services\Player;

use App\Models\User;
use App\Models\Player;

class PlayerService
{
    public function createProfile(array $data, User $user): Player
    {
        return Player::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function updateProfile(Player $player, array $data): Player
    {
        $player->update($data);

        return $player->fresh();
    }
}
