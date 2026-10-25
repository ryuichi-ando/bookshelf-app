<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookDestroyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍の登録者が書籍を削除できること
     */
    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '削除対象の書籍',
        ]);

        $response = $this->actingAs($user)->delete(
            route('books.destroy', $book)
        );

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * 他人の書籍を削除できないこと
     */
    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
            'title' => '所有者の書籍',
        ]);

        $response = $this->actingAs($otherUser)->delete(
            route('books.destroy', $book)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * 未ログインユーザーは書籍を削除できないこと
     */
    public function test_guest_cannot_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->delete(
            route('books.destroy', $book)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
