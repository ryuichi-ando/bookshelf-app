<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧を表示する
     */
    public function index(Request $request)
    {
        // ログインユーザーのお気に入り書籍を取得する
        $books = $request->user()
            ->favoriteBooks()
            ->orderBy('favorites.created_at', 'desc')
            ->paginate(10);

        // お気に入り一覧画面にデータを渡す
        return view('favorites.index', compact('books'));
    }

    /**
     * お気に入り登録・解除を切り替える
     */
    public function toggle(Request $request, Book $book)
    {
        // 登録済みなら解除、未登録なら追加する
        $request->user()
            ->favoriteBooks()
            ->toggle($book->id);

        // 元の画面に戻る
        return back()->with('success', 'お気に入りを更新しました。');
    }
}
