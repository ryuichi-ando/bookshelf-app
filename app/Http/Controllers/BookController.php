<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧
     */
    public function index()
    {
        $books = Book::with('genres')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('books.index', compact('books'));
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
        $book->setAttribute('published_date', $book->published_at);
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
}
