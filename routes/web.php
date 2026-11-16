<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;


// トップページにアクセスしたら書籍一覧へ移動
Route::redirect('/', '/login');

//書籍一覧・詳細（認証不要）
Route::get('/books', [BookController::class, 'index'])
    ->name('books.index');

// 評価ランキング（認証不要）
Route::get('/ranking', [RankingController::class, 'index'])
    ->name('ranking.index');

// 書籍の登録・編集・削除：ログイン必須
Route::middleware('auth')->group(function () {

    Route::get('/books/create', [BookController::class, 'create'])
        ->name('books.create');

    Route::post('/books', [BookController::class, 'store'])
        ->name('books.store');

    Route::get('/books/{book}/edit', [BookController::class, 'edit'])
        ->name('books.edit');

    Route::put('/books/{book}', [BookController::class, 'update'])
        ->name('books.update');

    Route::delete('/books/{book}', [BookController::class, 'destroy'])
        ->name('books.destroy');

    // ISBNから書籍情報を検索
    Route::get('/books/isbn/{isbn}', [BookController::class, 'searchByIsbn'])
        ->middleware('auth')
        ->name('books.isbn.search');

    // ジャンル一覧
    Route::get('/genres/create', [GenreController::class, 'create'])
        ->name('genres.create');

    // ジャンル登録処理
    Route::post('/genres', [GenreController::class, 'store'])
        ->name('genres.store');

    Route::get('/genres', [GenreController::class, 'index'])
        ->name('genres.index');

    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])
        ->name('genres.edit');

    Route::put('/genres/{genre}', [GenreController::class, 'update'])
        ->name('genres.update');

    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])
        ->name('genres.destroy');

    Route::get('/genres/{genre}', [GenreController::class, 'show'])
        ->name('genres.show');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');

    // お気に入り登録・解除
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

    // レビュー投稿
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    // レビュー編集画面
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
        ->name('reviews.edit');

    // レビュー更新
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])
        ->name('reviews.update');

    // レビュー削除（ログイン必須）
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // レビューへのいいね・いいね解除
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])
        ->name('reviews.like');

    // レポート機能
    Route::middleware('auth')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');
    });

    // 読書計画一覧
    Route::get('/reading-plans', [ReadingPlanController::class, 'index'])
        ->name('reading-plans.index');

    // 読書計画作成画面
    Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])
        ->name('reading-plans.create');

    // 読書計画登録
    Route::post('/reading-plans', [ReadingPlanController::class, 'store'])
        ->name('reading-plans.store');

    // 読書計画編集画面
    Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])
        ->name('reading-plans.edit');

    // 読書計画更新
    Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])
        ->name('reading-plans.update');

    // 読了処理
    Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])
        ->name('reading-plans.complete');

    // 読書計画削除
    Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])
        ->name('reading-plans.destroy');

    // 通知一覧
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // 通知を既読にする
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
});

// 書籍詳細（認証不要）
Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');