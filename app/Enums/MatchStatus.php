<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled'; // agendado
    case Completed = 'completed'; // concluído
    case Cancelled = 'cancelled'; // cancelado
    case Postponed = 'postponed'; // adiado
}
