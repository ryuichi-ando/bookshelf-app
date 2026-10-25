<?php

namespace App\Http\Controllers;

use App\Models\Book;

class RankingController extends Controller
{
    /**
     * 評価ランキングTOP10を表示する
     */
    public function index()
    {
        $rankedBooks = Book::query()
            // レビューの平均評価を取得
            ->withAvg('reviews', 'rating')

            // レビュー件数を取得
            ->withCount('reviews')

            // レビューが存在する書籍だけを対象にする
            ->whereHas('reviews')

            // 平均評価が高い順に並べる
            ->orderByDesc('reviews_avg_rating')

            // 平均評価が同じ場合はレビュー件数が多い順
            ->orderByDesc('reviews_count')

            // 上位10件を取得
            ->take(10)

            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
