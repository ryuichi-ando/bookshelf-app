<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 書籍API 
Route::prefix('v1')->group(function () {
    // 書籍一覧取得（認証不要） 
    Route::get('/books', [BookController::class, 'index'])
        ->name('api.v1.books.index');

    // 書籍詳細取得（認証不要） 
    Route::get('/books/{book}', [BookController::class, 'show'])
        ->name('api.v1.books.show');

    // 書籍新規登録・更新・削除（認証必須） 
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookController::class, 'store'])
            ->name('api.v1.books.store');
        Route::put('/books/{book}', [BookController::class, 'update'])
            ->name('api.v1.books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])
            ->name('api.v1.books.destroy');
    });
});