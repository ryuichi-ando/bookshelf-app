<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_by_book_title(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        Book::factory()->create([
            'title' => 'PHP入門',
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.index', ['keyword' => 'Laravel']));

        $response->assertOk();
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP入門');
    }

    public function test_search_by_author(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'author' => '田中花子',
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.index', ['keyword' => '山田太郎']));

        $response->assertOk();
        $response->assertSee('山田太郎');
        $response->assertDontSee('田中花子');
    }

    public function test_filter_by_genre(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create([
            'name' => '小説',
        ]);

        $genre2 = Genre::factory()->create([
            'name' => '技術書',
        ]);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book1->genres()->attach($genre1);
        $book2->genres()->attach($genre2);

        $response = $this->actingAs($user)
            ->get(route('books.index', [
                'genre' => $genre1->id,
            ]));

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($book1, $book2) {
            return $books->contains('id', $book1->id)
                && !$books->contains('id', $book2->id);
        });
    }

    public function test_sort_books_by_title(): void
    {
        $user = User::factory()->create();

        Book::factory()->create(['title' => 'C']);
        Book::factory()->create(['title' => 'A']);
        Book::factory()->create(['title' => 'B']);

        $response = $this->actingAs($user)
            ->get(route('books.index', ['sort' => 'title']));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) {
            return $books->pluck('title')->values()->all()
                === ['A', 'B', 'C'];
        });
    }

    public function test_search_and_filter_conditions_can_be_combined(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $target = Book::factory()->create([
            'title' => 'Laravel実践',
        ]);

        $other = Book::factory()->create([
            'title' => 'Laravel基礎',
        ]);

        $target->genres()->attach($genre);

        $response = $this->actingAs($user)
            ->get(route('books.index', [
                'keyword' => 'Laravel',
                'genre' => $genre->id,
            ]));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($target, $other) {
            return $books->contains('id', $target->id)
                && !$books->contains('id', $other->id);
        });
    }
}
