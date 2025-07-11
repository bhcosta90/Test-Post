<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Traits\AsApiController;

final class PostController
{
    use AsApiController;

    public function queryCommentsPostComments(Builder $query): Builder
    {
        return $query->where('id', '<', 2);
    }

    protected function model(): Model
    {
        return new Post();
    }
}
