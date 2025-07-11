<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerQraphQLExtension\Traits\AsApiController;

final class PostController
{
    use AsApiController;

    //    public function queryCommentsPostComments($query)
    //    {
    //        return $query->idLessThat(3);
    //    }
    //
    public function queryIndexCommentsPostComments($query)
    {
        return $query->idLessThat(4);
    }

    protected function model(): Model
    {
        return new Post();
    }
}
