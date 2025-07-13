<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

final class Author extends BaseModel
{
    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'media_able');
    }
}
