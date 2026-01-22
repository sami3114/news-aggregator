<?php

namespace App\Models\Article;

use Illuminate\Database\Eloquent\Builder;

trait Attributes
{
    /**
     * Scope for filtering by source.
     *
     * @param Builder $query
     * @param string $source
     * @return Builder
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for filtering by category
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    /**
     * Scope for filtering by category slug
     */
    public function scopeByCategorySlug($query, string $slug)
    {
        return $query->whereHas('categories', function ($q) use ($slug) {
            $q->where('categories.slug', $slug);
        });
    }

    /**
     * Scope for filtering by author
     */
    public function scopeByAuthor($query, int $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeBetweenDates($query, ?string $fromDate, ?string $toDate)
    {
        return $query
            ->when($fromDate, fn($q) => $q->whereDate('published_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('published_at', '<=', $toDate));
    }

    /**
     * Scope for keyword search.
     *
     * @param Builder $query
     * @param string $keyword
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        });
    }
}
