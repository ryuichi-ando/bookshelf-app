<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * マイ読書レポート
     */
    public function index()
    {
        $user = auth()->user();

        // ログインユーザーのレビューを取得
        $reviews = Review::where('user_id', $user->id);

        // 基本統計
        $totalReviews = (clone $reviews)->count();

        $averageRating = (clone $reviews)->avg('rating') ?? 0;

        // 読了冊数
        // 読書計画のモデル・ステータス構成に合わせて後ほど確認します
        $booksRead = 0;

        // 評価分布：評価1～5の件数
        $ratingDistribution = collect();

        for ($rating = 1; $rating <= 5; $rating++) {
            $ratingDistribution->put(
                $rating - 1,
                (clone $reviews)->where('rating', $rating)->count()
            );
        }

        // 高評価書籍 TOP5
        // ログインユーザー自身が付けた評価を基準にする
        $topRatedBooks = (clone $reviews)
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->orderByDesc('created_at')
            ->get()
            ->unique('book_id')
            ->take(5)
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->values();

        // ジャンル別評価傾向 TOP5
        $genreRatings = Genre::query()
            ->select([
                'genres.id',
                'genres.name',
            ])
            ->join('book_genre', 'genres.id', '=', 'book_genre.genre_id')
            ->join('books', 'books.id', '=', 'book_genre.book_id')
            ->join('reviews', 'reviews.book_id', '=', 'books.id')
            ->where('reviews.user_id', $user->id)
            ->groupBy('genres.id', 'genres.name')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->limit(5)
            ->get()
            ->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => (int) $genre->review_count,
                    'average_rating' => (float) $genre->average_rating,
                ];
            });

        // Bladeに渡すデータ
        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => (float) $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
