<?php

namespace App\Notifications\FriendlyMatch;

use App\Models\FriendlyMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MatchResultConfirmedNotification extends Notification
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
        return [
            'match_id' => $this->match->id,
            'kind' => 'match_result_confirmed',
            'home_goals' => $this->match->home_goals,
            'away_goals' => $this->match->away_goals,
        ];
    }
}
