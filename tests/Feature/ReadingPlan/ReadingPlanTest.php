<?php

namespace Tests\Feature\ReadingPlan;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分の読書計画一覧を表示できる
     */
    public function test_user_can_view_own_reading_plans(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($plans) use ($plan) {
            return $plans->contains('id', $plan->id);
        });
    }

    /**
     * 他のユーザーの読書計画は表示されない
     */
    public function test_user_cannot_view_other_users_reading_plans(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create();

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($plans) use ($otherPlan) {
            return !$plans->contains('id', $otherPlan->id);
        });
    }

    /**
     * ステータスで読書計画を絞り込める
     */
    public function test_user_can_filter_reading_plans_by_status(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $unreadPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Unread,
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Reading,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => ReadingPlanStatus::Unread->value,
            ]));

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($plans) use ($unreadPlan, $readingPlan) {
            return $plans->contains('id', $unreadPlan->id)
                && !$plans->contains('id', $readingPlan->id);
        });
    }

    /**
     * 不正なステータスを指定すると404になる
     */
    public function test_invalid_status_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index', [
                'status' => 999,
            ]));

        $response->assertNotFound();
    }

    /**
     * 読書計画の新規作成画面を表示できる
     */
    public function test_user_can_view_create_reading_plan_page(): void
    {
        $user = User::factory()->create();

        Book::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();

        $response->assertViewHas('books');
    }

    /**
     * 読書計画を登録できる
     */
    public function test_user_can_store_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reading-plans.store'), [
                'book_id' => $book->id,
                'target_date' => '2026-12-20',
            ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'due_date' => '2026-12-20',
            'status' => ReadingPlanStatus::Unread->value,
            'completed_at' => null,
        ]);
    }

    /**
     * 自分の読書計画の編集画面を表示できる
     */
    public function test_user_can_view_edit_reading_plan_page(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $plan));

        $response->assertOk();

        $response->assertViewHas('readingPlan');
    }

    /**
     * 他のユーザーの読書計画は編集できない
     */
    public function test_user_cannot_edit_other_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $plan));

        $response->assertForbidden();
    }

    /**
     * 読書計画を読了にできる
     */
    public function test_user_can_complete_reading_plan(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Unread,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $this->assertNotNull($plan->fresh()->completed_at);
    }

    /**
     * すでに読了済みの場合は再処理されない
     */
    public function test_completed_reading_plan_is_not_completed_again(): void
    {
        $user = User::factory()->create();

        $completedAt = now()->subDay();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => $completedAt,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertEquals(
            $completedAt->timestamp,
            $plan->fresh()->completed_at->timestamp
        );
    }

    /**
     * 他のユーザーの読書計画は読了にできない
     */
    public function test_user_cannot_complete_other_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ReadingPlanStatus::Unread,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reading-plans.complete', $plan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Unread->value,
        ]);
    }

    /**
     * 読書計画を削除できる
     */
    public function test_user_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $plan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    /**
     * 他のユーザーの読書計画は削除できない
     */
    public function test_user_cannot_delete_other_users_reading_plan(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reading-plans.destroy', $plan));

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
        ]);
    }
}
