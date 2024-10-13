<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmojiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emoji' => 'required|string|in:like,dislike',
            'slug' => 'required|string|exists:posts,slug',
        ];
    }

    public function attributes()
    {
        return [
            'emoji' => 'Emoji',
            'slug' => 'Post Slug',
        ];
    }
}
