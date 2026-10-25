<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ログイン済みユーザーが書籍を登録できること
     */
    public function test_authenticated_user_can_register_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            [
                'title' => '新しいテスト書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-01-01',
                'description' => '書籍登録のテストです。',
                'image_url' => null,
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => '新しいテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
        ]);

        $book = Book::where('isbn', '9781234567890')->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * 未ログインユーザーは書籍を登録できないこと
     */
    public function test_guest_cannot_register_book(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->post(
            route('books.store'),
            [
                'title' => '未ログインの書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-01-01',
                'description' => 'テスト用説明',
                'genres' => [$genre->id],
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('books', [
            'isbn' => '9781234567890',
        ]);
    }

    /**
     * 必須項目が不足している場合にバリデーションエラーになること
     */
    public function test_book_registration_fails_when_required_fields_are_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('books.store'),
            []
        );

        $response->assertSessionHasErrors([
            'title',
            'author',
            'isbn',
            'published_date',
            'genres',
        ]);
    }
}
