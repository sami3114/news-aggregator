<?php

namespace App\Models\Category;

use App\Models\Article;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait Relationships
{
    /**
     * Get the articles for this category
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }
}
