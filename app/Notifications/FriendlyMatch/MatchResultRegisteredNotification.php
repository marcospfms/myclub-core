<?php

namespace App\Notifications\FriendlyMatch;

use App\Models\FriendlyMatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MatchResultRegisteredNotification extends Notification
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
            'kind' => 'match_result_registered',
            'home_goals' => $this->match->home_goals,
            'away_goals' => $this->match->away_goals,
            'registered_by' => $this->match->result_registered_by,
        ];
    }
}
