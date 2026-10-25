<?php

namespace Tests\Feature\Ranking;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** * ランキング画面を表示できる */
    public function test_ranking_page_can_be_displayed(): void
    {
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
    }

    /** * 評価平均が高い書籍ほど上位に表示される */
    public function test_books_are_ordered_by_average_rating_descending(): void
    {
        $user = User::factory()->create();
        $highRatedBook = Book::factory()->create();
        $lowRatedBook = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $highRatedBook->id, 'rating' => 5,]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $lowRatedBook->id, 'rating' => 2,]);
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($highRatedBook, $lowRatedBook) {
            return $rankedBooks->first()->id === $highRatedBook->id && $rankedBooks->last()->id === $lowRatedBook->id;
        });
    }

    /** * レビューがない書籍はランキングに含まれない */
    public function test_books_without_reviews_are_excluded_from_ranking(): void
    {
        $user = User::factory()->create();
        $reviewedBook = Book::factory()->create();
        $unreviewedBook = Book::factory()->create();
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $reviewedBook->id, 'rating' => 4,]);
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($reviewedBook, $unreviewedBook) {
            return $rankedBooks->contains('id', $reviewedBook->id) && !$rankedBooks->contains('id', $unreviewedBook->id);
        });
    }

    /** * ランキングは上位10件まで表示される */
    public function test_ranking_displays_at_most_ten_books(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 12; $i++) {
            $book = Book::factory()->create();
            Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => ($i % 5) + 1,]);
        }
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }

    /** * 未ログインでもランキングを閲覧できる */
    public function test_guest_can_view_ranking(): void
    {
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
    }

    /** * 評価平均が同じ書籍もランキングに表示される */
    public function test_books_with_equal_average_rating_are_included(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        Review::factory()->create(['user_id' => $user1->id, 'book_id' => $book1->id, 'rating' => 4,]);
        Review::factory()->create(['user_id' => $user2->id, 'book_id' => $book2->id, 'rating' => 4,]);
        $response = $this->get(route('ranking.index'));
        $response->assertOk();
        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($book1, $book2) {
            return $rankedBooks->contains('id', $book1->id) && $rankedBooks->contains('id', $book2->id);
        });
    }
}
