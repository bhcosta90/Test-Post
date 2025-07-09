<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        return Comment::all();
    }

    public function store(CommentRequest $request)
    {
        return Comment::create($request->validated());
    }

    public function show(Comment $comment)
    {
        return $comment;
    }

    public function update(CommentRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return $comment;
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json();
    }
}
