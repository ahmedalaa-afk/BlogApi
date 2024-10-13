<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'content' => ['required', 'string'],
            'slug' => ['required', 'string', 'exists:posts,slug'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,icon,pdf,doc,docx'],
            'video' => ['nullable', 'mimes:mp4,mov,avi', 'max:20000'],
            'tags' => ['nullable',]
        ];
    }

    public function attributes()
    {
        return [
            'content' => 'Content',
            'slug' => 'Post Slug',
            'photo' => 'Photo',
            'video' => 'Video',
            'tags' => 'Tags'
        ];
    }
}
