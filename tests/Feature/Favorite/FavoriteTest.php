<?php

namespace Tests\Feature\Favorite;

use App\Models\Book;
use App\Models\User;
use finfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** * お気に入り一覧を表示できる */
    public function test_authenticated_user_can_view_favorite_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $user->favoriteBooks()->attach($book->id);
        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->contains('id', $book->id);
        });
    }

    /** * 他のユーザーのお気に入り書籍は表示されない */
    public function test_user_cannot_view_other_users_favorite_books(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $myBook = Book::factory()->create(['user_id' => $otherUser->id,]);
        $otherBook = Book::factory()->create(['user_id' => $otherUser->id,]);
        $user->favoriteBooks()->attach($myBook->id);
        $otherUser->favoriteBooks()->attach($otherBook->id);
        $response = $this->actingAs($user)->get(route('favorites.index'));
        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($myBook, $otherBook) {
            return $books->contains('id', $myBook->id)
                && !$books->contains('id', $otherBook->id);
        });
    }

    /** * お気に入りに登録できる */
    public function test_authenticated_user_can_add_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id,]);
    }

    /** * お気に入りを解除できる */
    public function test_authenticated_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $user->favoriteBooks()->attach($book->id);
        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'book_id' => $book->id,]);
    }

    /** * 未認証ユーザーはお気に入り一覧を表示できない */
    public function test_guest_cannot_view_favorite_books(): void
    {
        $response = $this->get(route('favorites.index'));
        $response->assertRedirect(route('login'));
    }

    /** * 未認証ユーザーはお気に入りに登録できない */
    public function test_guest_cannot_add_favorite(): void
    {
        $book = Book::factory()->create();
        $response = $this->post(route('favorites.toggle', $book));
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('favorites', ['book_id' => $book->id,]);
    }
}
