<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** * お気に入りがユーザーに紐づいていること */
    public function test_favorite_belongs_to_user(): void
    {
        $favorite = new Favorite();
        $this->assertInstanceOf(BelongsTo::class, $favorite->user());
        $this->assertEquals('user_id', $favorite->user()->getForeignKeyName());
    }

    /** * お気に入りが書籍に紐づいていること */
    public function test_favorite_belongs_to_book(): void
    {
        $favorite = new Favorite();
        $this->assertInstanceOf(BelongsTo::class, $favorite->book());
        $this->assertEquals('book_id', $favorite->book()->getForeignKeyName());
    }
}
