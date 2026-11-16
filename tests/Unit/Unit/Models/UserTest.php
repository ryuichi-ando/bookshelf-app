<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** * ユーザーが登録した書籍を取得できること */
    public function test_user_has_many_books(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->books());
        $this->assertEquals('user_id', $user->books()->getForeignKeyName());
    }

    /** * ユーザーが投稿したレビューを取得できること */
    public function test_user_has_many_reviews(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->reviews());
        $this->assertEquals('user_id', $user->reviews()->getForeignKeyName());
    }

    /** * ユーザーのお気に入り登録を取得できること */
    public function test_user_has_many_favorites(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->favorites());
        $this->assertEquals('user_id', $user->favorites()->getForeignKeyName());
    }

    /** * ユーザーがお気に入り登録した書籍を取得できること */
    public function test_user_belongs_to_many_favorite_books(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $user->favoriteBooks());
        $this->assertEquals('favorites', $user->favoriteBooks()->getTable());
    }

    /** * ユーザーがいいねしたレビューの中間レコードを取得できること */
    public function test_user_has_many_review_likes(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->reviewLikes());
        $this->assertEquals('user_id', $user->reviewLikes()->getForeignKeyName());
    }

    /** * ユーザーがいいねしたレビューを取得できること */
    public function test_user_belongs_to_many_liked_reviews(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(BelongsToMany::class, $user->likedReviews());
        $this->assertEquals('review_likes', $user->likedReviews()->getTable());
    }

    /** * ユーザーの読書予定を取得できること */
    public function test_user_has_many_reading_plans(): void
    {
        $user = User::factory()->create();
        $this->assertInstanceOf(HasMany::class, $user->readingPlans());
        $this->assertEquals('user_id', $user->readingPlans()->getForeignKeyName());
    }

    /** * パスワードがハッシュ化されること */
    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'password123',]);
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $user->password));
    }

    /** * パスワードとremember_tokenがJSON出力に含まれないこと */
    public function test_sensitive_attributes_are_hidden(): void
    {
        $user = User::factory()->create();
        $userArray = $user->toArray();
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }
}
