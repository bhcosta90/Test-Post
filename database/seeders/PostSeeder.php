<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PostSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::oldest()->first();

        DB::transaction(fn () => Post::factory()
            ->for($user)
            ->count(25)
            ->hasComments(25) // Each post will have 5 comments
            ->create());
    }
}
