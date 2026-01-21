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
     * Scope for filtering by category.
     *
     * @param Builder $query
     * @param string $category
     * @return Builder
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by author.
     *
     * @param Builder $query
     * @param string $author
     * @return Builder
     */
    public function scopeByAuthor(Builder $query, string $author): Builder
    {
        return $query->where('author', 'like', "%{$author}%");
    }

    /**
     * Scope for filtering by date range.
     *
     * @param Builder $query
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return Builder
     */
    public function scopeBetweenDates(Builder $query, ?string $fromDate, ?string $toDate): Builder
    {
        if ($fromDate) {
            $query->whereDate('published_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('published_at', '<=', $toDate);
        }

        return $query;
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
