<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required'],
            'post_id' => ['required', 'exists:posts'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
