<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入を許可するカラム
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * JSONなどに出力しないカラム
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性の型変換
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * ユーザーが登録した書籍
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * ユーザーが投稿したレビュー
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * ユーザーのお気に入り登録
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * ユーザーがお気に入り登録した書籍
     */
    public function favoriteBooks(): BelongsToMany
    {
        return $this->belongsToMany(
            Book::class,
            'favorites',
            'user_id',
            'book_id'
        )->withTimestamps();
    }

    /**
     * ユーザーがいいねしたレビューの中間レコード
     */
    public function reviewLikes(): HasMany
    {
        return $this->hasMany(ReviewLike::class);
    }

    /**
     * ユーザーがいいねしたレビュー
     */
    public function likedReviews(): BelongsToMany
    {
        return $this->belongsToMany(
            Review::class,
            'review_likes',
            'user_id',
            'review_id'
        )->withTimestamps();
    }

    /**
     * ユーザーの読書予定
     */
    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }
}
