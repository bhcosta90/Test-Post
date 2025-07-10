<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body'    => ['required'],
            'post_id' => ['required', 'exists:posts,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
