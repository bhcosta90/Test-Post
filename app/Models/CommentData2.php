<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CommentData2 extends Model
{
    use SoftDeletes;

    public function commentData(): BelongsTo
    {
        return $this->belongsTo(CommentData::class);
    }
}
