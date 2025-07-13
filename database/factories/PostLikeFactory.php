<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class PostLikeFactory extends Factory
{
    protected $model = PostLike::class;

    public function definition(): array
    {
        return [
            'post_id'    => Post::factory(),
            'like'       => $this->faker->numberBetween(0, 5),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
