<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\ControllerBasicsExtension\Traits\AsGraphQLController;

final class PostController
{
    use AsGraphQLController;

    //    public function queryCommentsPostComments($query)
    //    {
    //        return $query->idLessThat(3);
    //    }
    //
    //    public function queryIndexCommentsPostComments($query)
    //    {
    //        return $query->idLessThat(4);
    //    }

    protected function model(): Model
    {
        return new Post();
    }
}
