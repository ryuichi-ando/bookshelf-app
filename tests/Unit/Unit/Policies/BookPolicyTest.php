<?php

namespace Tests\Unit\Unit\Policies;

use App\Models\Book;
use App\Models\User;
use App\Policies\BookPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 書籍の登録者は更新できること
     */
    public function test_owner_can_update_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new BookPolicy();

        $this->assertTrue(
            $policy->update($user, $book)
        );
    }

    /**
     * 書籍の登録者以外は更新できないこと
     */
    public function test_other_user_cannot_update_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new BookPolicy();

        $this->assertFalse(
            $policy->update($otherUser, $book)
        );
    }

    /**
     * 書籍の登録者は削除できること
     */
    public function test_owner_can_delete_book(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new BookPolicy();

        $this->assertTrue(
            $policy->delete($user, $book)
        );
    }

    /**
     * 書籍の登録者以外は削除できないこと
     */
    public function test_other_user_cannot_delete_book(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new BookPolicy();

        $this->assertFalse(
            $policy->delete($otherUser, $book)
        );
    }
}
