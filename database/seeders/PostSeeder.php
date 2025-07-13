<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PostSeeder extends Seeder
{
    public function run(): void
    {
        $author = Author::factory()->create();
        $author->medias()->create([
            'name' => fake()->sentence(3),
        ]);

        DB::transaction(fn () => Post::factory()
            ->for($author)
            ->create())
            ->each(function (Post $post) {
                $post->medias()->create([
                    'name' => fake()->sentence(3),
                ]);
                PostLike::factory(random_int(3, 10))->for($post)->create();
                Comment::factory(random_int(3, 10))->for($post)->create()->each(function (Comment $comment) {
                    CommentLike::factory(random_int(3, 10))->for($comment)->create();
                });
            });
    }
}
