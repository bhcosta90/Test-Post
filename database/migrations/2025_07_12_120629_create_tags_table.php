<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained('tags');
            $table->foreignId('post_id')->constrained('posts');
        });

        Schema::create('comment_tag', function (Blueprint $table) {
            $table->foreignId('comment_id')->constrained('comments');
            $table->foreignId('tag_id')->constrained('tags');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('comment_tag');
        Schema::dropIfExists('tags');
    }
};
