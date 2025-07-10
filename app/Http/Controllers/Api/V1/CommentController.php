<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Traits\AsApiController;

final class CommentController
{
    use AsApiController;

    protected function model(): Model
    {
        return new Comment();
    }
}
