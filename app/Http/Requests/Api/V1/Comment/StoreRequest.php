<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api\V1\Comment;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
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
