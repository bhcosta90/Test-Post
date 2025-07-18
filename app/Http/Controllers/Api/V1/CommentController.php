<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerBasicsExtension\Traits\AsGraphQLController;

final class CommentController
{
    use AsGraphQLController;

    protected function model(): Model
    {
        return new Comment();
    }
}
