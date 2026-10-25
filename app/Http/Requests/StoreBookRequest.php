<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * 書籍登録用
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'author' => [
                'required',
                'string',
                'max:255',
            ],
            'isbn' => [
                'required',
                'string',
                'size:13',
                'regex:/^[0-9]{13}$/',
                'unique:books,isbn',
            ],
            'published_date' => [
                'required',
                'date',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'genres' => [
                'required',
                'array',
                'min:1',
            ],
            'genres.*' => [
                'required',
                'integer',
                'distinct',
                'exists:genres,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '書籍タイトルを入力してください。',
            'author.required' => '著者名を入力してください。',
            'isbn.required' => 'ISBNを入力してください。',
            'isbn.size' => 'ISBNは13桁で入力してください。',
            'isbn.regex' => 'ISBNは半角数字13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',
            'image_url.url' => '画像URLは正しい形式で入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.exists' => '存在しないジャンルが指定されています。',
            'published_date.required' => '出版日を入力してください。',
            'published_date.date' => '正しい出版日を入力してください。',
        ];
    }
}
