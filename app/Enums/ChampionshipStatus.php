<?php

namespace App\Enums;

enum ChampionshipStatus: string
{
    case Draft = 'draft';
    case Enrollment = 'enrollment';
    case Active = 'active';
    case Finished = 'finished';
    case Archived = 'archived';
    case Cancelled = 'cancelled';
}
