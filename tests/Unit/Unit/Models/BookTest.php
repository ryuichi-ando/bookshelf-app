<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
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
}
