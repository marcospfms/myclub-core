<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $badges = [
            ['name' => 'golden_ball', 'label_key' => 'badges.golden_ball.label', 'description_key' => 'badges.golden_ball.description', 'scope' => 'championship', 'icon' => 'award'],
            ['name' => 'top_scorer', 'label_key' => 'badges.top_scorer.label', 'description_key' => 'badges.top_scorer.description', 'scope' => 'championship', 'icon' => 'goal'],
            ['name' => 'best_assist', 'label_key' => 'badges.best_assist.label', 'description_key' => 'badges.best_assist.description', 'scope' => 'championship', 'icon' => 'handshake'],
            ['name' => 'best_goalkeeper', 'label_key' => 'badges.best_goalkeeper.label', 'description_key' => 'badges.best_goalkeeper.description', 'scope' => 'championship', 'icon' => 'shield'],
            ['name' => 'fair_play', 'label_key' => 'badges.fair_play.label', 'description_key' => 'badges.fair_play.description', 'scope' => 'championship', 'icon' => 'heart_handshake'],
            ['name' => 'hat_trick', 'label_key' => 'badges.hat_trick.label', 'description_key' => 'badges.hat_trick.description', 'scope' => 'career', 'icon' => 'flame'],
            ['name' => 'iron_man', 'label_key' => 'badges.iron_man.label', 'description_key' => 'badges.iron_man.description', 'scope' => 'championship', 'icon' => 'medal'],
            ['name' => 'unbeaten_champion', 'label_key' => 'badges.unbeaten_champion.label', 'description_key' => 'badges.unbeaten_champion.description', 'scope' => 'championship', 'icon' => 'trophy'],
            ['name' => 'top_scorer_season', 'label_key' => 'badges.top_scorer_season.label', 'description_key' => 'badges.top_scorer_season.description', 'scope' => 'seasonal', 'icon' => 'target'],
            ['name' => 'best_assist_season', 'label_key' => 'badges.best_assist_season.label', 'description_key' => 'badges.best_assist_season.description', 'scope' => 'seasonal', 'icon' => 'sparkles'],
            ['name' => 'mvp_streak', 'label_key' => 'badges.mvp_streak.label', 'description_key' => 'badges.mvp_streak.description', 'scope' => 'career', 'icon' => 'zap'],
            ['name' => 'loyal_player', 'label_key' => 'badges.loyal_player.label', 'description_key' => 'badges.loyal_player.description', 'scope' => 'career', 'icon' => 'flag'],
            ['name' => 'rising_star', 'label_key' => 'badges.rising_star.label', 'description_key' => 'badges.rising_star.description', 'scope' => 'seasonal', 'icon' => 'star'],
            ['name' => 'clean_sweep', 'label_key' => 'badges.clean_sweep.label', 'description_key' => 'badges.clean_sweep.description', 'scope' => 'championship', 'icon' => 'shield_check'],
        ];

        DB::table('badge_types')->upsert(array_map(
            fn (array $badge): array => array_merge($badge, [
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            $badges,
        ), ['name'], ['label_key', 'description_key', 'icon', 'scope', 'updated_at']);
    }
}
