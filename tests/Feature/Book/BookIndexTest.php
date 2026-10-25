<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍一覧ページが正常に表示されること
     */
    public function test_book_index_page_can_be_displayed(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
    }

    /**
     * 登録済みの書籍が一覧の表示対象に含まれること
     */
    public function test_registered_books_are_included_in_book_list(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'テスト書籍',
        ]);

        $response = $this->get(route('books.index'));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->contains('id', $book->id);
        });
    }

    /**
     * 未ログインでも書籍一覧を閲覧できること
     */
    public function test_guest_can_view_book_index(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertOk();
    }
}
