<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    /** * 書籍一覧取得 * * GET /api/v1/books */
    public function index(Request $request): JsonResponse
    {
        $query = Book::query()->with('genres')->withAvg('reviews', 'rating')->withCount('reviews');
        // キーワード検索（タイトル・著者） 
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")->orWhere('author', 'like', "%{$keyword}%");
            });
        }
        // ジャンルによる絞り込み 
        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre_id'));
            });
        }
        // ページネーション 
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100));
        $books = $query->orderByDesc('created_at')->paginate($perPage);
        // JSON形式で返す 
        return response()->json([
            'data' => $books->getCollection()
                ->map(fn(Book $book) => $this->formatBook($book))->values(),
            'current_page' => $books->currentPage(),
            'per_page' => $books->perPage(),
            'total' => $books->total(),
            'last_page' => $books->lastPage(),
        ]);
    }

    /** * 書籍詳細取得 * * GET /api/v1/books/{book} */
    public function show(Book $book): JsonResponse
    {
        $book->load(['genres', 'reviews.user',]);
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');
        return response()->json(['data' => array_merge($this->formatBook($book), ['reviews' => $book->reviews->map(fn($review) => ['id' => $review->id, 'user_id' => $review->user_id, 'user_name' => $review->user?->name, 'rating' => $review->rating, 'comment' => $review->comment, 'created_at' => $review->created_at?->toIso8601String(),])->values(),]),]);
    }

    /** * 書籍新規登録 * * POST /api/v1/books */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $book = DB::transaction(function () use ($request, $validated) {
            $genres = $validated['genres'];
            unset($validated['genres']);
            $book = Book::create([...$validated, 'user_id' => $request->user()->id,]);
            $book->genres()->sync($genres);
            return $book;
        });
        $book->load('genres');
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');
        return response()->json(['message' => '書籍を登録しました。', 'data' => $this->formatBook($book),], 201);
    }

    /** * 書籍更新 * * PUT /api/v1/books/{book} */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        Gate::authorize('update', $book);
        $validated = $request->validated();
        DB::transaction(function () use ($book, $validated) {
            if (array_key_exists('genres', $validated)) {
                $book->genres()->sync($validated['genres']);
                unset($validated['genres']);
            }$book->update($validated);
        });
        $book->load('genres');
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');
        return response()->json(['message' => '書籍を更新しました。', 'data' => $this->formatBook($book),]);
    }

    /** * 書籍削除 * * DELETE /api/v1/books/{book} */
    public function destroy(Book $book): JsonResponse
    {
        Gate::authorize('delete', $book);
        DB::transaction(function () use ($book) {
            // ジャンルとの関連を解除 
            $book->genres()->detach();
            // お気に入りを削除 
            $book->favorites()->delete();
            // 読書予定を削除 
            $book->readingPlans()->delete();
            // レビューを削除 
            $book->reviews()->delete();
            // 書籍を削除 
            $book->delete();
        });
        return response()->json(null, 204);
    }

    /** * 書籍情報をJSON用に整形 */
    private function formatBook(Book $book): array
    {
        return [
            'id' => $book->id,
            'user_id' => $book->user_id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_at' => $book->published_at
                ? \Illuminate\Support\Carbon::parse($book->published_at)->format('Y-m-d')
                : null,
            'description' => $book->description,
            'image_url' => $book->image_url,
            'genres' => $book->genres->map(fn($genre) => ['id' => $genre->id, 'name' => $genre->name,])->values(),
            'average_rating' => $book->reviews_avg_rating !== null ? round((float) $book->reviews_avg_rating, 2) : null,
            'reviews_count' => $book->reviews_count,
            'created_at' => $book->created_at?->toIso8601String(),
            'updated_at' => $book->updated_at?->toIso8601String(),
        ];
    }
}
