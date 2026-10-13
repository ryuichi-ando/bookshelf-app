<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->numberBetween(
            ReadingPlan::STATUS_UNREAD,
            ReadingPlan::STATUS_COMPLETED
        );

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'due_date' => fake()->date(),
            'status' => $status,
            'completed_at' => $status === ReadingPlan::STATUS_COMPLETED
                ? now()
                : null,
        ];
    }
}
