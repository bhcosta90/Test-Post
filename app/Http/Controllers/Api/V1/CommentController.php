<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

final class CommentController extends Controller
{
    protected function model(): Model
    {
        return new Comment();
    }
}
