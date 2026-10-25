<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ジャンルが複数の書籍に紐づいていること
     */
    public function test_genre_belongs_to_many_books(): void
    {
        $genre = Genre::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $genre->books()->attach([
            $book1->id,
            $book2->id,
        ]);

        $this->assertCount(2, $genre->books);

        $this->assertTrue(
            $genre->books->contains('id', $book1->id)
        );

        $this->assertTrue(
            $genre->books->contains('id', $book2->id)
        );
    }

    /**
     * ジャンル名が正しく保存されること
     */
    public function test_genre_name_is_stored_correctly(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'ミステリー',
        ]);

        $this->assertEquals('ミステリー', $genre->name);
    }
}
