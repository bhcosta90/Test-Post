<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class CommentLikeFactory extends Factory
{
    protected $model = CommentLike::class;

    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'like'       => $this->faker->numberBetween(0, 5),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
