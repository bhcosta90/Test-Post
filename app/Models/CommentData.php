<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CommentData extends Model
{
    use SoftDeletes;

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function commentsData2(): HasMany
    {
        return $this->hasMany(CommentData2::class);
    }
}
