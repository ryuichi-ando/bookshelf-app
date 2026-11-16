<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_books(): void
    {
        $response = $this->getJson('/api/v1/books');

        $response->assertOk();
    }

    public function test_guest_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', []);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'isbn' => '9781234567890',
                'genres' => [],
            ]);

        // 実際のバリデーションに合わせて入力項目を調整
        $response->assertSuccessful();
    }

    public function test_guest_cannot_update_book(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson(
            "/api/v1/books/{$book->id}",
            []
        );

        $response->assertUnauthorized();
    }

    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", [
                'title' => '変更後',
                'author' => $book->author,
                'isbn' => $book->isbn,
            ]);

        $response->assertForbidden();
    }

    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertForbidden();
    }
}
