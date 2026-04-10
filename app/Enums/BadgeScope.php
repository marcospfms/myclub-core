<?php

namespace App\Enums;

enum BadgeScope: string
{
    case Championship = 'championship'; // campeonato
    case Friendly = 'friendly'; // amistoso
    case Career = 'career'; // carreira
    case Seasonal = 'seasonal'; // temporada
}
