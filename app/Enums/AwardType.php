<?php

namespace App\Enums;

enum AwardType: string
{
    case GoldenBall = 'golden_ball';
    case TopScorer = 'top_scorer';
    case BestAssist = 'best_assist';
    case BestGoalkeeper = 'best_goalkeeper';
    case FairPlay = 'fair_play';
}
