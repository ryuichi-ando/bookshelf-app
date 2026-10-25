<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    /** * レビューを投稿する */
    public function store(Request $request, Book $book)
    {
        // 投稿内容を検証する 
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        // ログインユーザーと書籍を紐づけてレビューを保存する 
        $book->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // 書籍詳細画面に戻る 
        return redirect()
            ->route('books.show', $book)->with('success', 'レビューを投稿しました。');
    }

    /** * レビュー編集画面を表示する */
    public function edit(Review $review)
    {
        // 投稿者本人かどうかを確認する 
        Gate::authorize('update', $review);

        // 編集画面を表示する 
        return view('reviews.edit', compact('review'));
    }

    /** * レビューを更新する */
    public function update(Request $request, Review $review)
    {
        // 投稿者本人かどうかを確認する 
        Gate::authorize('update', $review);
        // 入力内容を検証する 
        $validated = $request->validate(
            [
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'comment' => ['nullable', 'string', 'max:2000'],
            ]
        );

        // レビューを更新する 
        $review->update(['rating' => $validated['rating'], 'comment' => $validated['comment'] ?? null,]);
        // 書籍詳細画面に戻る 
        return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました。');
    }

    public function destroy(Review $review)
    {
        // 投稿者本人かどうかを確認する
        Gate::authorize('delete', $review);

        // レビューが紐づいている書籍IDを取得する
        $bookId = $review->book_id;

        // レビューを削除する
        $review->delete();

        // 書籍詳細画面に戻る
        return redirect()
            ->route('books.show', $bookId)
            ->with('success', 'レビューを削除しました。');
    }

    /**
     * レビューへのいいね・いいね解除
     */
    public function like(Request $request, Review $review)
    {
        $user = $request->user();

        // すでにいいねしている場合は解除、していない場合は追加
        $user->likedReviews()->toggle($review->id);

        return redirect()
            ->route('books.show', $review->book_id)
            ->with('success', 'いいねを更新しました。');
    }
}