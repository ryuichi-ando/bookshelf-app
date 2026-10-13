<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->count() < 5) {
            throw new \RuntimeException(
                'FavoriteSeederを実行する前にUserSeederとBookSeederを実行してください。'
            );
        }

        foreach ($users as $user) {
            $favoriteCount = rand(3, 5);

            $bookIds = $books
                ->random($favoriteCount)
                ->pluck('id')
                ->all();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
