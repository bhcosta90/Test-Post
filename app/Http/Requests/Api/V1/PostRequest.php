<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api\V1;

use App\Models\Enum\PostStatusEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'   => ['required'],
            'user_id' => [
                'sometimes',
                Rule::requiredIf((bool) $this->route('post')),
                'exists:' . User::class . ',id',
            ],
            'status' => [
                'sometimes',
                Rule::requiredIf((bool) $this->route('post')),
                Rule::enum(PostStatusEnum::class),
            ],
            'tags.*.name' => [
                'required',
                'string',
                'max:1000',
            ],
            'comments.*.body' => [
                'required',
                'string',
                'max:1000',
            ],
            'comments.*.commentsData.*.name' => [
                'nullable',
                'string',
                'max:30',
            ],
            'comments.*.commentsData.*.commentsData2.*.name' => [
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
