<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            throw new \RuntimeException(
                'BookSeederを実行する前にUserSeederを実行してください。'
            );
        }

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'description' => '猫の視点から人間社会を描いた夏目漱石の代表作。',
                'genres' => ['小説'],
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'description' => '人間関係やコミュニケーションの原則を解説する書籍。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'description' => '読みやすく保守しやすいコードを書くための実践的な技法。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'description' => '主体的な生き方と効果的な行動原則を解説する書籍。',
                'genres' => ['ビジネス', '自己啓発'],
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'description' => '正義感の強い主人公の経験を描いた小説。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'description' => '人類の歴史を大きな視点から読み解く書籍。',
                'genres' => ['歴史', '科学'],
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'description' => '保守性の高いソフトウェアを開発するための考え方を解説する書籍。',
                'genres' => ['技術書'],
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'description' => '対話形式でアドラー心理学の考え方を紹介する書籍。',
                'genres' => ['自己啓発'],
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'description' => '芸人の世界を背景に、人間関係や生き方を描いた小説。',
                'genres' => ['小説'],
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'description' => 'データに基づいて世界を正しく理解するための考え方を紹介する書籍。',
                'genres' => ['ビジネス', '科学'],
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'description' => 'コンテナが世界の物流と経済に与えた影響を解説する書籍。',
                'genres' => ['ビジネス', '歴史'],
            ],
        ];

        foreach ($books as $index => $data) {
            $genreNames = $data['genres'];

            unset($data['genres']);

            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    ...$data,
                    'user_id' => $users->random()->id,
                    'image_url' => sprintf(
                        'https://placehold.co/200x300/e2e8f0/475569?text=%d',
                        $index + 1
                    ),
                ]
            );

            $genreIds = Genre::whereIn('name', $genreNames)
                ->pluck('id')
                ->all();

            $book->genres()->sync($genreIds);
        }
    }
}
