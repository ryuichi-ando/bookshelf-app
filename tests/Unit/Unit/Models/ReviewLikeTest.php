<?php

namespace Tests\Unit\Unit\Models;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** * いいねがユーザーに紐づいていること */
    public function test_review_like_belongs_to_user(): void
    {
        $reviewLike = new ReviewLike();
        $this->assertInstanceOf(BelongsTo::class, $reviewLike->user());
        $this->assertEquals('user_id', $reviewLike->user()->getForeignKeyName());
    }

    /** * いいねがレビューに紐づいていること */
    public function test_review_like_belongs_to_review(): void
    {
        $reviewLike = new ReviewLike();
        $this->assertInstanceOf(BelongsTo::class, $reviewLike->review());
        $this->assertEquals('review_id', $reviewLike->review()->getForeignKeyName());
    }
}
