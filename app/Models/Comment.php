<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    #[Scope]
    public function idLessThat(Builder $query, int $id): Builder
    {
        return $query->where('id', '<', $id);
    }

    public function getCanDeleteAttribute(): bool
    {
        return true;
    }

    public function canUpdate(): Attribute
    {
        return Attribute::get(fn (): false => false);
    }
}
