<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CommentData extends Model
{
    use SoftDeletes;

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
