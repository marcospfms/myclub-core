<?php

namespace App\Notifications\FriendlyMatch;

use App\Models\FriendlyMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FriendlyMatchConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly FriendlyMatch $match,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->match->loadMissing(['awayTeam.team']);

        return [
            'match_id' => $this->match->id,
            'kind' => 'friendly_match_confirmed',
            'away_team_name' => $this->match->awayTeam->team->name,
            'scheduled_at' => $this->match->scheduled_at,
        ];
    }
}
