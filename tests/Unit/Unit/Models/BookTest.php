<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use App\Models\Favorite;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍がユーザーに紐づいていること
     */
    public function test_book_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $book->user);
        $this->assertEquals($user->id, $book->user->id);
    }

    /**
     * 書籍が複数のジャンルに紐づいていること
     */
    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book->genres()->attach([
            $genre1->id,
            $genre2->id,
        ]);

        $this->assertCount(2, $book->genres);
        $this->assertTrue(
            $book->genres->contains('id', $genre1->id)
        );
        $this->assertTrue(
            $book->genres->contains('id', $genre2->id)
        );
    }

    /**
     * 書籍が複数のレビューを持てること
     */
    public function test_book_has_many_reviews(): void
    {
        $book = Book::factory()->create();

        Review::factory()->count(2)->create([
            'book_id' => $book->id,
        ]);

        $this->assertCount(2, $book->reviews);
    }

    /**
     * 書籍モデルの属性が正しく設定されていること
     */
    public function test_book_has_expected_fillable_attributes(): void
    {
        $book = new Book();

        $this->assertEquals(
            [
                'user_id',
                'title',
                'author',
                'isbn',
                'published_at',
                'description',
                'image_url',
            ],
            $book->getFillable()
        );
    }

    /**
     * 公開日がCarbon型で取得できること
     */
    public function test_published_date_returns_carbon_instance(): void
    {
        $book = Book::factory()->create([
            'published_at' => '2025-04-01',
        ]);

        $this->assertInstanceOf(Carbon::class, $book->published_date);

        $this->assertEquals(
            '2025-04-01',
            $book->published_date->format('Y-m-d')
        );
    }

    /**
     * 公開日が未設定の場合、nullを返すこと
     */
    public function test_published_date_returns_null_when_empty(): void
    {
        $book = new Book([
            'published_at' => null,
        ]);

        $this->assertNull($book->published_date);
    }

    /**
     * 書籍がお気に入り登録レコードを複数持てること
     */
    public function test_book_has_many_favorites(): void
    {
        $book = Book::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book->favorites()->create([
            'user_id' => $user1->id,
        ]);

        $book->favorites()->create([
            'user_id' => $user2->id,
        ]);

        $this->assertCount(2, $book->favorites);
    }

    /**
     * 書籍をお気に入り登録したユーザーを取得できること
     */
    public function test_book_belongs_to_many_favorited_users(): void
    {
        $book = Book::factory()->create();

        $user = User::factory()->create();

        $book->favoritedByUsers()->attach($user->id);

        $this->assertCount(1, $book->favoritedByUsers);

        $this->assertEquals(
            $user->id,
            $book->favoritedByUsers->first()->id
        );
    }

    /**
     * 書籍が複数の読書予定を持てること
     */
    public function test_book_has_many_reading_plans(): void
    {
        $book = Book::factory()->create();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book->readingPlans()->create([
            'user_id' => $user1->id,
            'due_date' => '2026-12-01',
            'status' => ReadingPlanStatus::Unread,
        ]);

        $book->readingPlans()->create([
            'user_id' => $user2->id,
            'due_date' => '2026-12-15',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $this->assertCount(2, $book->readingPlans);
    }
}
