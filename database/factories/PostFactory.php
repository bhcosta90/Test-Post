<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Enum\PostStatusEnum;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

final class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title'      => $this->faker->sentence(3),
            'status'     => $this->faker->randomElement(PostStatusEnum::cases()),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
