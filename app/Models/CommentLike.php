<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CommentLike extends BaseModel
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
