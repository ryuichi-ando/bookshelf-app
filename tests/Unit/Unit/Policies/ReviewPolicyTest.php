<?php

namespace Tests\Unit\Unit\Policies;

use App\Models\Review;
use App\Models\User;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログインユーザーはレビュー一覧を閲覧できる
     */
    public function test_user_can_view_any_reviews(): void
    {
        $user = User::factory()->create();

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    /**
     * ログインユーザーはレビュー詳細を閲覧できる
     */
    public function test_user_can_view_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->view($user, $review));
    }

    /**
     * ログインユーザーはレビューを登録できる
     */
    public function test_user_can_create_review(): void
    {
        $user = User::factory()->create();

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->create($user));
    }

    /**
     * 投稿者はレビューを編集できる
     */
    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->update($user, $review));
    }

    /**
     * 投稿者以外はレビューを編集できない
     */
    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse($policy->update($otherUser, $review));
    }

    /**
     * 投稿者はレビューを削除できる
     */
    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->delete($user, $review));
    }

    /**
     * 投稿者以外はレビューを削除できない
     */
    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse($policy->delete($otherUser, $review));
    }

    /**
     * 投稿者はレビューを復元できる
     */
    public function test_owner_can_restore_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->restore($user, $review));
    }

    /**
     * 投稿者以外はレビューを復元できない
     */
    public function test_other_user_cannot_restore_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse($policy->restore($otherUser, $review));
    }

    /**
     * 投稿者はレビューを完全削除できる
     */
    public function test_owner_can_force_delete_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue($policy->forceDelete($user, $review));
    }

    /**
     * 投稿者以外はレビューを完全削除できない
     */
    public function test_other_user_cannot_force_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse($policy->forceDelete($otherUser, $review));
    }
}
