<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $book = $this->route('book');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'isbn' => [
                'sometimes',
                'required',
                'digits:13',
                Rule::unique('books', 'isbn')->ignore($book?->id),
            ],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'genres' => ['sometimes', 'required', 'array', 'min:1'],
            'genres.*' => [
                'required',
                'integer',
                'distinct',
                'exists:genres,id',
            ],
        ];
    }
}
