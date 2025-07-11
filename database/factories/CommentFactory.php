<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'body'       => $this->faker->sentence(15),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'post_id'    => Post::factory(),
        ];
    }
}
