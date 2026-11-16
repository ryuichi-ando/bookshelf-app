<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * 書籍一覧
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // 書籍とジャンルを取得するためのクエリを作成
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating');

        // 1. キーワード検索
        $keyword = $request->input('keyword');

        if ($keyword !== null && trim($keyword) !== '') {
            $keyword = trim($keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('author', 'like', '%' . $keyword . '%');
            });
        }

        // 2. ジャンル絞り込み
        $genre = $request->input('genre');

        if ($genre !== null && $genre !== '') {
            $query->whereHas('genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre);
            });
        }

        // 3. 並び替え
        $sort = $request->input('sort', 'latest');

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'title':
                $query->orderBy('title', 'asc');
                break;

            case 'rating':
                $query->orderByRaw(
                    'CASE WHEN reviews_avg_rating IS NULL THEN 1 ELSE 0 END'
                )->orderByDesc('reviews_avg_rating');
                break;

            case 'latest':
            default:
                $sort = 'latest';
                $query->orderByDesc('created_at');
                break;
        }

        // 4. ページネーション
        $books = $query
            ->paginate(10)
            ->withQueryString();

        // ジャンル一覧もビューに渡す
        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍詳細
     */
    public function show(Book $book)
    {
        $book->load([
            'user',
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);
        #$book->setAttribute('published_date', $book->published_at);
        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面
     */
    public function create()
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍登録
     */
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $book = Book::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_at' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres']);
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍編集画面
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();

        $book->setAttribute('published_date', $book->published_at);

        $book->load('genres');

        $selectedGenreIds = $book->genres
            ->pluck('id')
            ->all();

        return view(
            'books.edit',
            compact('book', 'genres', 'selectedGenreIds')
        );
    }

    /**
     * 書籍更新
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated) {
            $book->update([
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_at' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres']);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍削除
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        DB::transaction(function () use ($book) {
            // レビューのいいねを削除
            $reviewIds = $book->reviews()->pluck('id');

            if ($reviewIds->isNotEmpty()) {
                DB::table('review_likes')
                    ->whereIn('review_id', $reviewIds)
                    ->delete();
            }

            // 書籍に紐づくレビューを削除
            $book->reviews()->delete();

            // お気に入りを削除
            $book->favorites()->delete();

            // 読書計画を削除
            $book->readingPlans()->delete();

            // ジャンルとの関連付けを解除
            $book->genres()->detach();

            // 書籍を削除
            $book->delete();
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }

    /**
     * ISBNからGoogle Books APIで書籍情報を検索
     */
    public function searchByIsbn(string $isbn): JsonResponse
    {
        // ISBNが13桁の数字であることを確認
        if (!preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁の数字で入力してください。',
            ], 422);
        }

        try {
            // Google Books APIを呼び出す
            $response = Http::timeout(10)->get(
                'https://www.googleapis.com/books/v1/volumes',
                [
                    'q' => 'isbn:' . $isbn,
                    'maxResults' => 1,
                    'key' => config('services.google_books.api_key'),
                ]
            );

            // API通信に失敗した場合
            if ($response->failed()) {
                return response()->json([
                    'error' => '書籍情報の取得に失敗しました。時間をおいて再度お試しください。',
                ], 502);
            }

            $data = $response->json();

            // 該当する書籍がない場合
            if (empty($data['items'])) {
                return response()->json([
                    'error' => '該当する書籍が見つかりませんでした。',
                ], 404);
            }

            $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

            // 必要な書籍情報を抽出
            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => implode('、', $volumeInfo['authors'] ?? []),
                'published_date' => $volumeInfo['publishedDate'] ?? '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // 接続エラーやタイムアウト
            return response()->json([
                'error' => 'Google Books APIとの通信に失敗しました。',
            ], 502);
        }
    }

}
