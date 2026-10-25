<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍の登録者が書籍を編集できること
     */
    public function test_owner_can_update_book(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '編集前の書籍',
        ]);

        $response = $this->actingAs($user)->put(
            route('books.update', $book),
            [
                'title' => '編集後の書籍',
                'author' => '編集後の著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-02-01',
                'description' => '編集後の説明',
                'image_url' => null,
                'genres' => [$genre->id],
            ]
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => '編集後の書籍',
            'author' => '編集後の著者',
            'isbn' => '9781234567890',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /**
     * 他人の書籍を編集できないこと
     */
    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
        ]);

        $response = $this->actingAs($otherUser)->put(
            route('books.update', $book),
            [
                'title' => '不正な変更',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'published_date' => '2026-02-01',
                'description' => 'テスト用説明',
                'genres' => [$genre->id],
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '所有者の書籍',
        ]);
    }

    /**
     * 未ログインユーザーは書籍を編集できないこと
     */
    public function test_guest_cannot_update_book(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '編集対象の書籍',
        ]);

        $response = $this->put(
            route('books.update', $book),
            []
        );

        $response->assertRedirect(route('login'));
    }
}
