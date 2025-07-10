<?php

declare(strict_types = 1);

use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', fn (Request $request) => $request->user())->middleware('auth:sanctum');

Route::prefix('v1')->group(function (): void {
    Route::apiResource('/posts', PostController::class);
    Route::apiResource('/comments', CommentController::class);
});
