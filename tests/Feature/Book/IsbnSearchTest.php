<?php

namespace Tests\Feature\Book;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_isbn_search_returns_book_information(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Laravel入門',
                            'authors' => ['山田太郎'],
                            'publishedDate' => '2025-01-01',
                            'description' => 'Laravelの入門書',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(
            route('books.isbn.search', ['isbn' => '9781234567890'])
        );

        $response->assertOk()
            ->assertJson([
                'title' => 'Laravel入門',
                'author' => '山田太郎',
            ]);
    }

    public function test_isbn_search_returns_404_when_book_not_found(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $response = $this->getJson(
            route('books.isbn.search', ['isbn' => '9781234567890'])
        );

        $response->assertNotFound();
    }

    public function test_isbn_search_rejects_invalid_isbn(): void
    {
        $response = $this->getJson(
            route('books.isbn.search', ['isbn' => '123'])
        );

        $response->assertUnprocessable();
    }

    public function test_isbn_search_returns_502_when_api_fails(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $response = $this->getJson(
            route('books.isbn.search', ['isbn' => '9781234567890'])
        );

        $response->assertStatus(502);
    }
}
