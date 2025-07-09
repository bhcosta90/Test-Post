<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function getActionsAttribute(): array
    {
        return [
            'can_delete' => true,
            'can_update' => true,
        ];
    }
}
