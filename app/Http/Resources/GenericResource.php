<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use App\Http\Presenters\GenericPresenter;
use Illuminate\Http\Resources\Json\JsonResource;

final class GenericResource extends JsonResource
{
    protected array $onlyFields = [];

    public function toArray($request): array
    {
        return ($presenter = app(GenericPresenter::class))->transform($this->resource, [
            'fields' => $request->input('fields', ''),
            // 'pagination' => $presenter->extractPagination($request->all()),
        ]);
    }
}
