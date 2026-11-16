<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * レビューがユーザーに紐づいていること
     */
    public function test_review_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /**
     * レビューが書籍に紐づいていること
     */
    public function test_review_belongs_to_book(): void
    {
        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /**
     * レビューの評価が1～5の整数であること
     */
    public function test_review_rating_is_stored_correctly(): void
    {
        $review = Review::factory()->create([
            'rating' => 5,
        ]);

        $this->assertEquals(5, $review->rating);
    }

    /**
     * レビューのコメントが保存されること
     */
    public function test_review_comment_is_stored_correctly(): void
    {
        $comment = 'とても面白い本でした。';

        $review = Review::factory()->create([
            'comment' => $comment,
        ]);

        $this->assertEquals($comment, $review->comment);
    }

    /**
     * レビューに対するいいねの中間レコードを取得できること
     */
    public function test_review_has_many_likes(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // いいねの中間レコードを作成
        \Illuminate\Support\Facades\DB::table('review_likes')->insert([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // リレーション経由で取得できることを確認
        $this->assertCount(1, $review->likes);
        $this->assertEquals($user->id, $review->likes->first()->user_id);
    }

    /**
     * レビューにいいねしたユーザーを取得できること
     */
    public function test_review_belongs_to_many_liked_users(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // いいねを登録
        $review->likedByUsers()->attach($user->id);

        // いいねしたユーザーを取得できることを確認
        $this->assertCount(1, $review->likedByUsers);
        $this->assertEquals($user->id, $review->likedByUsers->first()->id);
    }
}
