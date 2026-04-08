<?php

namespace App\Enums;

enum BadgeScope: string
{
    case Championship = 'championship';
    case Friendly = 'friendly';
    case Career = 'career';
    case Seasonal = 'seasonal';
}
