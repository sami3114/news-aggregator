<?php

namespace App\Models\Author;

use App\Models\Article;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Relationships
{
    /**
     * Get the articles written by this author
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
