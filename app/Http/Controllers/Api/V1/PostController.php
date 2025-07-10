<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Traits\AsApiController;

final class PostController
{
    use AsApiController;

    protected function model(): Model
    {
        return new Post();
    }
}
