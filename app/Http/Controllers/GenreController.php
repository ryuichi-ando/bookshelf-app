<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     */
    public function index()
    {
        // 各ジャンルに紐づく書籍数を取得する
        $genres = Genre::withCount('books')
            ->orderBy('name')
            ->get();

        // ジャンル一覧画面を表示する
        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル詳細を表示する
     */
    public function show(Genre $genre)
    {
        $books = $genre->books()
            ->with('genres')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /** * ジャンル登録画面を表示する */
    public function create()
    {
        return view('genres.create');
    }

    /** * ジャンルを登録する */
    public function store(Request $request)
    {
        $validated = $request->validate(
            ['name' => ['required', 'string', 'max:255', 'unique:genres,name',],],
            ['name.required' => 'ジャンル名を入力してください。', 'name.string' => 'ジャンル名は文字列で入力してください。', 'name.max' => 'ジャンル名は255文字以内で入力してください。', 'name.unique' => 'このジャンル名はすでに登録されています。',]
        );
        Genre::create(['name' => $validated['name'],]);
        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました。');
    }

    /** * ジャンルを更新する */
    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate(
            ['name' => ['required', 'string', 'max:255', Rule::unique('genres', 'name')->ignore($genre->id),],],
            ['name.required' => 'ジャンル名を入力してください。', 'name.string' => 'ジャンル名は文字列で入力してください。', 'name.max' => 'ジャンル名は255文字以内で入力してください。', 'name.unique' => 'このジャンル名はすでに登録されています。',]
        );
        $genre->update(['name' => $validated['name'],]);
        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました。');
    }

    /** * ジャンルを削除する */
    public function destroy(Genre $genre)
    {
        // 書籍が紐づいている場合は削除を禁止する 
        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')->with('error', 'このジャンルには書籍が紐づいているため、削除できません。');
        }
        DB::transaction(function () use ($genre) {
            $genre->delete();
        });
        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました。');
    }

    /**
     * ジャンル編集画面を表示する
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }
}
