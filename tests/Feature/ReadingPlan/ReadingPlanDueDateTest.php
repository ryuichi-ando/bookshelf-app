<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanDueDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_reading_plan_due_date(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'due_date' => '2026-12-01',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $plan), [
                'target_date' => '2026-12-15',
                'status' => $plan->status->value,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'due_date' => '2026-12-15',
        ]);
    }

    public function test_user_cannot_change_other_users_due_date(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
            'due_date' => '2026-12-01',
        ]);

        $response = $this->actingAs($otherUser)
            ->put(route('reading-plans.update', $plan), [
                'target_date' => '2026-12-15',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'due_date' => '2026-12-01',
        ]);
    }

    public function test_completed_plan_cannot_change_due_date(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => \App\Enums\ReadingPlanStatus::Completed,
            'completed_at' => now(),
            'due_date' => '2026-12-01',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reading-plans.update', $plan), [
                'target_date' => '2026-12-15',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'due_date' => '2026-12-01',
        ]);
    }
}
