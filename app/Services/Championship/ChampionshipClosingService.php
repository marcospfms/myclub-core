<?php

namespace App\Services\Championship;

use App\Enums\AwardType;
use App\Enums\BadgeScope;
use App\Enums\ChampionshipStatus;
use App\Models\BadgeType;
use App\Models\Championship;
use App\Models\ChampionshipAward;
use App\Models\ChampionshipMatch;
use App\Models\ChampionshipMatchHighlight;
use App\Models\PlayerBadge;
use App\Models\PlayerMembership;
use Illuminate\Support\Facades\DB;

class ChampionshipClosingService
{
    public function finish(Championship $championship): void
    {
        DB::transaction(function () use ($championship): void {
            $championship->update(['status' => ChampionshipStatus::Finished]);

            ChampionshipAward::query()
                ->where('championship_id', $championship->id)
                ->delete();

            PlayerBadge::query()
                ->where('championship_id', $championship->id)
                ->delete();

            $this->calculateAwards($championship);
            $this->grantBadges($championship);
            $this->detectCareerBadges($championship);
        });
    }

    private function detectCareerBadges(Championship $championship): void
    {
        $matchIds = $this->championshipMatchIds($championship);

        if ($matchIds === []) {
            return;
        }

        $hatTrickBadge = BadgeType::query()
            ->where('name', 'hat_trick')
            ->where('scope', BadgeScope::Career->value)
            ->first();

        if (! $hatTrickBadge) {
            return;
        }

        $playerIds = ChampionshipMatchHighlight::query()
            ->whereIn('championship_match_id', $matchIds)
            ->where('goals', '>=', 3)
            ->pluck('player_membership_id')
            ->map(fn (int $membershipId): ?int => PlayerMembership::query()->find($membershipId)?->player_id)
            ->filter()
            ->unique()
            ->values();

        foreach ($playerIds as $playerId) {
            $alreadyAwarded = PlayerBadge::query()
                ->where('player_id', $playerId)
                ->where('badge_type_id', $hatTrickBadge->id)
                ->where('championship_id', $championship->id)
                ->exists();

            if ($alreadyAwarded) {
                continue;
            }

            PlayerBadge::create([
                'player_id' => $playerId,
                'badge_type_id' => $hatTrickBadge->id,
                'championship_id' => $championship->id,
                'awarded_at' => now(),
                'notes' => "Campeonato: {$championship->name}",
                'year' => now()->year,
            ]);
        }
    }

    private function calculateAwards(Championship $championship): void
    {
        $matchIds = $this->championshipMatchIds($championship);

        if ($matchIds === []) {
            return;
        }

        $topScorer = ChampionshipMatchHighlight::query()
            ->whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(goals) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->orderBy('player_membership_id')
            ->first();

        if (($topScorer->total ?? 0) > 0) {
            $playerId = PlayerMembership::query()->find($topScorer->player_membership_id)?->player_id;

            if ($playerId !== null) {
                ChampionshipAward::create([
                    'championship_id' => $championship->id,
                    'player_id' => $playerId,
                    'award_type' => AwardType::TopScorer,
                    'value' => (int) $topScorer->total,
                ]);
            }
        }

        $topAssist = ChampionshipMatchHighlight::query()
            ->whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(assists) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->orderBy('player_membership_id')
            ->first();

        if (($topAssist->total ?? 0) > 0) {
            $playerId = PlayerMembership::query()->find($topAssist->player_membership_id)?->player_id;

            if ($playerId !== null) {
                ChampionshipAward::create([
                    'championship_id' => $championship->id,
                    'player_id' => $playerId,
                    'award_type' => AwardType::BestAssist,
                    'value' => (int) $topAssist->total,
                ]);
            }
        }

        $goldenBall = ChampionshipMatchHighlight::query()
            ->whereIn('championship_match_id', $matchIds)
            ->where('is_mvp', true)
            ->selectRaw('player_membership_id, COUNT(*) as total')
            ->groupBy('player_membership_id')
            ->orderByDesc('total')
            ->orderBy('player_membership_id')
            ->first();

        if ($goldenBall !== null) {
            $playerId = PlayerMembership::query()->find($goldenBall->player_membership_id)?->player_id;

            if ($playerId !== null) {
                ChampionshipAward::create([
                    'championship_id' => $championship->id,
                    'player_id' => $playerId,
                    'award_type' => AwardType::GoldenBall,
                    'value' => (int) $goldenBall->total,
                ]);
            }
        }

        $fairPlay = ChampionshipMatchHighlight::query()
            ->whereIn('championship_match_id', $matchIds)
            ->selectRaw('player_membership_id, SUM(yellow_cards + red_cards) as cards')
            ->groupBy('player_membership_id')
            ->having('cards', 0)
            ->orderBy('player_membership_id')
            ->first();

        if ($fairPlay !== null) {
            $playerId = PlayerMembership::query()->find($fairPlay->player_membership_id)?->player_id;

            if ($playerId !== null) {
                ChampionshipAward::create([
                    'championship_id' => $championship->id,
                    'player_id' => $playerId,
                    'award_type' => AwardType::FairPlay,
                    'value' => 0,
                ]);
            }
        }
    }

    private function grantBadges(Championship $championship): void
    {
        $badgeMap = [
            AwardType::GoldenBall->value => 'golden_ball',
            AwardType::TopScorer->value => 'top_scorer',
            AwardType::BestAssist->value => 'best_assist',
            AwardType::FairPlay->value => 'fair_play',
        ];

        $awards = $championship->awards()->get();

        foreach ($awards as $award) {
            $badgeTypeName = $badgeMap[$award->award_type->value] ?? null;

            if ($badgeTypeName === null) {
                continue;
            }

            $badgeType = BadgeType::query()->where('name', $badgeTypeName)->first();

            if (! $badgeType) {
                continue;
            }

            PlayerBadge::create([
                'player_id' => $award->player_id,
                'badge_type_id' => $badgeType->id,
                'championship_id' => $championship->id,
                'awarded_at' => now(),
                'notes' => "Campeonato: {$championship->name}",
                'year' => now()->year,
            ]);
        }
    }

    /**
     * @return array<int, int>
     */
    private function championshipMatchIds(Championship $championship): array
    {
        return ChampionshipMatch::query()
            ->whereHas('round.phase', fn ($query) => $query->where('championship_id', $championship->id))
            ->pluck('id')
            ->all();
    }
}
