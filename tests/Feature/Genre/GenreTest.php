<?php

namespace Tests\Feature\Genre;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** * ジャンル一覧を表示できる */
    public function test_genre_index_page_can_be_displayed(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => 'ミステリー']);
        $response = $this->actingAs($user)->get(route('genres.index'));
        $response->assertOk();
    }

    /** * ジャンル詳細を表示できる */
    public function test_genre_show_page_can_be_displayed(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'ファンタジー',]);
        $response = $this->actingAs($user)->get(route('genres.show', $genre));
        $response->assertOk();
    }

    /** * 存在しないジャンルは404になる */
    public function test_nonexistent_genre_returns_404(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('genres.show', 999999));
        $response->assertNotFound();
    }

    /** * ログイン済みユーザーがジャンルを登録できる */
    public function test_authenticated_user_can_create_genre(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('genres.store'), ['name' => 'SF',]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('genres', ['name' => 'SF',]);
    }

    /** * 未ログインユーザーはジャンルを登録できない */
    public function test_guest_cannot_create_genre(): void
    {
        $response = $this->post(route('genres.store'), ['name' => 'SF',]);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('genres', ['name' => 'SF',]);
    }

    /** * ジャンル名が未入力の場合はエラーになる */
    public function test_genre_creation_requires_name(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('genres.store'), ['name' => '',]);
        $response->assertSessionHasErrors(['name']);
    }

    /** * 重複したジャンル名は登録できない */
    public function test_duplicate_genre_name_cannot_be_created(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create(['name' => 'ミステリー',]);
        $response = $this->actingAs($user)->post(route('genres.store'), ['name' => 'ミステリー',]);
        $response->assertSessionHasErrors(['name']);
    }

    /** * ジャンル名を編集できる */
    public function test_genre_can_be_updated(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧ジャンル名',]);
        $response = $this->actingAs($user)->put(route('genres.update', $genre), ['name' => '新ジャンル名',]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '新ジャンル名',]);
    }

    /** * 書籍に関連付けられていないジャンルは削除できる */
    public function test_unused_genre_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '削除対象ジャンル',]);
        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));
        $this->assertDatabaseMissing('genres', ['id' => $genre->id,]);
    }

    /** * 書籍に関連付けられたジャンルは削除できない */
    public function test_genre_with_books_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '使用中ジャンル',]);
        $book = Book::factory()->create(['user_id' => $user->id,]);
        $book->genres()->attach($genre->id);
        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));
        $this->assertDatabaseHas('genres', ['id' => $genre->id, 'name' => '使用中ジャンル',]);
    }
}
