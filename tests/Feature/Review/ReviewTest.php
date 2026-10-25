<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** * ログイン済みユーザーがレビューを登録できる */
    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 5, 'comment' => 'とても面白い本でした。',]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 5, 'comment' => 'とても面白い本でした。',]);
    }

    /** * 未ログインユーザーはレビューを登録できない */
    public function test_guest_cannot_create_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->post(route('reviews.store', $book), ['rating' => 5, 'comment' => '未ログインでの投稿テスト',]);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('reviews', ['book_id' => $book->id, 'comment' => '未ログインでの投稿テスト',]);
    }

    /** * 評価が1未満の場合はバリデーションエラーになる */
    public function test_review_rating_must_be_at_least_one(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 0, 'comment' => '評価範囲のテスト',]);
        $response->assertSessionHasErrors(['rating']);
    }

    /** * 評価が5を超える場合はバリデーションエラーになる */
    public function test_review_rating_must_not_exceed_five(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 6, 'comment' => '評価範囲のテスト',]);
        $response->assertSessionHasErrors(['rating']);
    }

    /** * コメントが2000文字を超える場合はエラーになる */
    public function test_review_comment_cannot_exceed_2000_characters(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->actingAs($user)->post(route('reviews.store', $book), ['rating' => 4, 'comment' => str_repeat('あ', 2001),]);
        $response->assertSessionHasErrors(['comment']);
    }

    /** * 自分のレビューを編集できる */
    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 3, 'comment' => '編集前のコメント',]);
        $response = $this->actingAs($user)->put(route('reviews.update', $review), ['rating' => 5, 'comment' => '編集後のコメント',]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 5, 'comment' => '編集後のコメント',]);
    }

    /** * 他人のレビューは編集できない */
    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id,]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id, 'rating' => 3, 'comment' => '元のコメント',]);
        $response = $this->actingAs($otherUser)->put(route('reviews.update', $review), ['rating' => 1, 'comment' => '不正な変更',]);
        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'comment' => '元のコメント', 'rating' => 3,]);
    }

    /** * 自分のレビューを削除できる */
    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id,]);
        $response = $this->actingAs($user)->delete(route('reviews.destroy', $review));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id,]);
    }

    /** * 他人のレビューは削除できない */
    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id,]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id,]);
        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));
        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', ['id' => $review->id,]);
    }

    /** * レビューにいいねできる */
    public function test_user_can_like_review(): void
    {
        $owner = User::factory()->create();
        $liker = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id,]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id,]);
        $response = $this->actingAs($liker)->post(route('reviews.like', $review));
        $this->assertDatabaseHas('review_likes', ['user_id' => $liker->id, 'review_id' => $review->id,]);
    }

    /** * もう一度いいねすると解除される */
    public function test_user_can_unlike_review(): void
    {
        $owner = User::factory()->create();
        $liker = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id,]);
        $review = Review::factory()->create(['user_id' => $owner->id, 'book_id' => $book->id,]);

        // 最初にいいねする 
        $liker->likedReviews()->attach($review->id);

        // もう一度押して解除する 
        $response = $this->actingAs($liker)->post(route('reviews.like', $review));
        $this->assertDatabaseMissing('review_likes', ['user_id' => $liker->id, 'review_id' => $review->id,]);
    }
}
