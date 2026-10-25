<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookApiSearchTest extends TestCase
{
    use RefreshDatabase;

    /** * 書籍名のキーワードで検索できる */
    public function test_books_can_be_searched_by_title_keyword(): void
    {
        Book::factory()->create(['title' => 'Laravel入門', 'author' => '山田太郎',]);
        Book::factory()->create(['title' => 'PHP入門', 'author' => '佐藤花子',]);
        $response = $this->getJson('/api/v1/books?keyword=Laravel');
        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Laravel入門',]);
        $response->assertJsonMissing(['title' => 'PHP入門',]);
    }

    /** * 著者名のキーワードで検索できる */
    public function test_books_can_be_searched_by_author_keyword(): void
    {
        Book::factory()->create(['title' => '書籍A', 'author' => '山田太郎',]);
        Book::factory()->create(['title' => '書籍B', 'author' => '佐藤花子',]);
        $response = $this->getJson('/api/v1/books?keyword=山田太郎');
        $response->assertOk();
        $response->assertJsonFragment(['author' => '山田太郎',]);
        $response->assertJsonMissing(['author' => '佐藤花子',]);
    }

    /** * 指定したジャンルの書籍だけ取得できる */
    public function test_books_can_be_filtered_by_genre(): void
    {
        $genreA = Genre::factory()->create(['name' => '小説',]);
        $genreB = Genre::factory()->create(['name' => '技術書',]);
        $bookA = Book::factory()->create(['title' => '小説の本',]);
        $bookB = Book::factory()->create(['title' => '技術書の本',]);
        $bookA->genres()->attach($genreA->id);
        $bookB->genres()->attach($genreB->id);
        $response = $this->getJson('/api/v1/books?genre_id=' . $genreA->id);
        $response->assertOk();
        $response->assertJsonFragment(['title' => '小説の本',]);
        $response->assertJsonMissing(['title' => '技術書の本',]);
    }

    /** * per_pageで1ページあたりの取得件数を指定できる */
    public function test_books_api_respects_per_page_parameter(): void
    {
        Book::factory()->count(5)->create();
        $response = $this->getJson('/api/v1/books?per_page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    /** * pageで取得するページを変更できる */
    public function test_books_api_can_return_second_page(): void
    {
        Book::factory()->count(5)->create();
        $response = $this->getJson('/api/v1/books?per_page=2&page=2');
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('current_page', 2);
    }

    /** * per_pageの上限が100件であることを確認する */
    public function test_books_api_limits_per_page_to_one_hundred(): void
    {
        Book::factory()->count(105)->create();
        $response = $this->getJson('/api/v1/books?per_page=150');
        $response->assertOk();
        $response->assertJsonCount(100, 'data');
    }

    /** * 検索結果がない場合も正常に応答する */
    public function test_books_api_returns_empty_results_when_no_books_match(): void
    {
        Book::factory()->create(['title' => 'Laravel入門',]);
        $response = $this->getJson('/api/v1/books?keyword=存在しない書籍');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
