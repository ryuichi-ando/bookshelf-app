<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** * 書籍一覧APIから書籍を取得できる */
    public function test_books_api_returns_book_list(): void
    {
        $book = Book::factory()->create(['title' => 'テスト書籍', 'author' => 'テスト著者',]);
        $response = $this->getJson('/api/v1/books');
        $response->assertOk();
        $response->assertJsonFragment(['title' => 'テスト書籍', 'author' => 'テスト著者',]);
    }

    /** * 書籍一覧APIは未認証でも利用できる */
    public function test_guest_can_access_books_api(): void
    {
        Book::factory()->create();
        $response = $this->getJson('/api/v1/books');
        $response->assertOk();
    }

    /** * 書籍詳細APIから指定した書籍を取得できる */
    public function test_books_api_returns_book_details(): void
    {
        $book = Book::factory()->create(['title' => '詳細テスト書籍', 'author' => '詳細テスト著者',]);
        $response = $this->getJson('/api/v1/books/' . $book->id);
        $response->assertOk();
        $response->assertJsonFragment(['title' => '詳細テスト書籍', 'author' => '詳細テスト著者',]);
    }

    /** * 書籍詳細APIは未認証でも利用できる */
    public function test_guest_can_access_book_detail_api(): void
    {
        $book = Book::factory()->create();
        $response = $this->getJson('/api/v1/books/' . $book->id);
        $response->assertOk();
    }

    /** * 存在しない書籍を指定すると404が返る */
    public function test_books_api_returns_404_for_nonexistent_book(): void
    {
        $response = $this->getJson('/api/v1/books/999999');
        $response->assertNotFound();
    }

    /** * 書籍一覧APIから複数の書籍を取得できる */
    public function test_books_api_returns_multiple_books(): void
    {
        $book1 = Book::factory()->create(['title' => '書籍A',]);
        $book2 = Book::factory()->create(['title' => '書籍B',]);
        $response = $this->getJson('/api/v1/books');
        $response->assertOk();
        $response->assertJsonFragment(['title' => '書籍A',]);
        $response->assertJsonFragment(['title' => '書籍B',]);
    }
}
