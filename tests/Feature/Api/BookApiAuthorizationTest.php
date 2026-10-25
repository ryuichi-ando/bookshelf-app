<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** * 未認証ユーザーは書籍を登録できない */
    public function test_guest_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', ['title' => 'テスト書籍', 'author' => 'テスト著者', 'isbn' => '9784123456789',]);
        $response->assertUnauthorized();
    }

    /** * 未認証ユーザーは書籍を更新できない */
    public function test_guest_cannot_update_book(): void
    {
        $book = Book::factory()->create();
        $response = $this->putJson('/api/v1/books/' . $book->id, ['title' => '更新後のタイトル']);
        $response->assertUnauthorized();
    }

    /** * 未認証ユーザーは書籍を削除できない */
    public function test_guest_cannot_delete_book(): void
    {
        $book = Book::factory()->create();
        $response = $this->deleteJson('/api/v1/books/' . $book->id);
        $response->assertUnauthorized();
    }

    /** * 書籍の所有者は自分の書籍を更新できる */
    public function test_owner_can_update_own_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        Sanctum::actingAs($user);
        $response = $this->putJson('/api/v1/books/' . $book->id, ['title' => '更新後のタイトル',]);
        $response->assertSuccessful();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '更新後のタイトル',]);
    }

    /** * 他のユーザーは所有者の書籍を更新できない */
    public function test_user_cannot_update_another_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id, 'title' => '元のタイトル',]);
        Sanctum::actingAs($otherUser);
        $response = $this->putJson('/api/v1/books/' . $book->id, ['title' => '不正な更新',]);
        $response->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => '元のタイトル',]);
    }

    /** * 書籍の所有者は自分の書籍を削除できる */
    public function test_owner_can_delete_own_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        Sanctum::actingAs($user);
        $response = $this->deleteJson('/api/v1/books/' . $book->id);
        $response->assertSuccessful();
        $this->assertDatabaseMissing('books', ['id' => $book->id,]);
    }

    /** * 他のユーザーは所有者の書籍を削除できない */
    public function test_user_cannot_delete_another_users_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id,]);
        Sanctum::actingAs($otherUser);
        $response = $this->deleteJson('/api/v1/books/' . $book->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id,]);
    }
}
