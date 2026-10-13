<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::with('user')->get();

        foreach ($reviews as $review) {
            $eligibleUsers = $users
                ->reject(fn(User $user) => $user->id === $review->user_id)
                ->values();

            if ($eligibleUsers->isEmpty()) {
                continue;
            }

            $likeCount = rand(
                0,
                min(3, $eligibleUsers->count())
            );

            if ($likeCount === 0) {
                continue;
            }

            $userIds = $eligibleUsers
                ->random($likeCount)
                ->pluck('id')
                ->all();

            $review->likedByUsers()->syncWithoutDetaching($userIds);
        }
    }
}
