<?php

declare(strict_types = 1);

namespace App\Http\Requests\Api\V1;

use App\Models\Enum\PostStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'title'   => ['required'],
            'status'  => [
                'sometimes',
                Rule::requiredIf((bool) $this->route('post')),
                Rule::enum(PostStatusEnum::class),
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
