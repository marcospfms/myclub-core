<?php

namespace Tests\Feature\Phase3;

use App\Enums\ChampionshipFormat;
use App\Enums\ChampionshipStatus;
use App\Models\Category;
use App\Models\Championship;
use App\Models\User;
use App\Policies\ChampionshipPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_can_update_manage_lifecycle_enrollment_and_match(): void
    {
        $creator = User::factory()->create();
        $championship = $this->createChampionship($creator, ChampionshipStatus::Draft);
        $policy = new ChampionshipPolicy;

        $this->assertTrue($policy->update($creator, $championship));
        $this->assertTrue($policy->manageLifecycle($creator, $championship));
        $this->assertTrue($policy->manageEnrollment($creator, $championship));
        $this->assertTrue($policy->manageMatch($creator, $championship));
        $this->assertTrue($policy->delete($creator, $championship));
    }

    public function test_non_creator_cannot_manage_championship(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $championship = $this->createChampionship($creator, ChampionshipStatus::Draft);
        $policy = new ChampionshipPolicy;

        $this->assertFalse($policy->update($otherUser, $championship));
        $this->assertFalse($policy->delete($otherUser, $championship));
        $this->assertFalse($policy->manageLifecycle($otherUser, $championship));
        $this->assertFalse($policy->manageEnrollment($otherUser, $championship));
        $this->assertFalse($policy->manageMatch($otherUser, $championship));
    }

    public function test_only_admin_can_cancel_active_championship(): void
    {
        $creator = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $championship = $this->createChampionship($creator, ChampionshipStatus::Active);
        $policy = new ChampionshipPolicy;

        $this->assertFalse($policy->cancelActive($creator, $championship));
        $this->assertTrue($policy->cancelActive($admin, $championship));
    }

    public function test_delete_requires_draft_even_for_admin(): void
    {
        $creator = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $championship = $this->createChampionship($creator, ChampionshipStatus::Enrollment);
        $policy = new ChampionshipPolicy;

        $this->assertFalse($policy->delete($creator, $championship));
        $this->assertFalse($policy->delete($admin, $championship));
        $this->assertTrue($policy->manageLifecycle($admin, $championship));
    }

    public function test_any_authenticated_user_can_enroll_team(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $championship = $this->createChampionship($creator, ChampionshipStatus::Enrollment);
        $policy = new ChampionshipPolicy;

        $this->assertTrue($policy->enroll($creator, $championship));
        $this->assertTrue($policy->enroll($otherUser, $championship));
    }

    private function createChampionship(User $creator, ChampionshipStatus $status): Championship
    {
        $category = Category::factory()->create();

        return Championship::create([
            'created_by' => $creator->id,
            'category_id' => $category->id,
            'name' => 'Championship Test',
            'description' => 'Policy scenario',
            'format' => ChampionshipFormat::League,
            'status' => $status,
            'max_players' => 20,
        ]);
    }
}
