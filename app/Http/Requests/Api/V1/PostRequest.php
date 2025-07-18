<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'     => ['required'],
            'author_id' => [
                'sometimes',
                Rule::requiredIf((bool) $this->route('post')),
                'exists:' . User::class . ',id',
            ],
            'tags.*.name' => [
                'required',
                'string',
                'max:50',
            ],
            'comments.*.body' => [
                'required',
                'string',
                'max:1000',
            ],
            'comments.*.comment_likes.*.like' => [
                'required',
                'numeric',
                'max:5',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
