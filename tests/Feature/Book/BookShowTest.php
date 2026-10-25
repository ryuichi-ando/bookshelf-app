<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍詳細ページが正常に表示されること
     */
    public function test_book_show_page_can_be_displayed(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '詳細テスト書籍',
        ]);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
    }

    /**
     * 指定した書籍が詳細ページの表示対象に含まれること
     */
    public function test_correct_book_is_passed_to_show_view(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '詳細確認用の書籍',
        ]);

        $response = $this->get(route('books.show', $book));

        $response->assertOk();

        $response->assertViewHas('book', function ($viewBook) use ($book) {
            return $viewBook->id === $book->id;
        });
    }

    /**
     * 存在しない書籍にアクセスすると404になること
     */
    public function test_nonexistent_book_returns_404(): void
    {
        $response = $this->get(route('books.show', 999999));

        $response->assertNotFound();
    }
}
