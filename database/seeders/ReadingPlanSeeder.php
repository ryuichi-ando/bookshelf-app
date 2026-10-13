<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();

        $books = Book::orderBy('id')->take(6)->get();

        if (!$yamada || !$suzuki || $books->count() < 6) {
            throw new \RuntimeException(
                'ReadingPlanSeederにはユーザー5人と書籍11冊のSeeder実行が必要です。'
            );
        }

        $today = Carbon::today();

        $plans = [
            [
                'user_id' => $yamada->id,
                'book_id' => $books[0]->id,
                'due_date' => $today->copy()->addDays(3),
                'status' => ReadingPlan::STATUS_READING,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[1]->id,
                'due_date' => $today->copy(),
                'status' => ReadingPlan::STATUS_READING,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[2]->id,
                'due_date' => $today->copy()->subDays(3),
                'status' => ReadingPlan::STATUS_READING,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[3]->id,
                'due_date' => $today->copy()->addDays(7),
                'status' => ReadingPlan::STATUS_READING,
                'completed_at' => null,
            ],
            [
                'user_id' => $yamada->id,
                'book_id' => $books[4]->id,
                'due_date' => $today->copy()->subDays(10),
                'status' => ReadingPlan::STATUS_COMPLETED,
                'completed_at' => $today->copy()->subDays(5),
            ],
            [
                'user_id' => $suzuki->id,
                'book_id' => $books[5]->id,
                'due_date' => $today->copy()->addDays(5),
                'status' => ReadingPlan::STATUS_READING,
                'completed_at' => null,
            ],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::create($plan);
        }
    }
}
