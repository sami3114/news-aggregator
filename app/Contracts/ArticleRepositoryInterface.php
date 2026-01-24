<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    /**
     * Get all articles.
     *
     * @param array $filters
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], ?int $perPage = null): LengthAwarePaginator;

    /**
     * Bulk upsert articles with authors and categories
     *
     * @param array $articles
     * @param array $categoryPivot
     * @return int Number of articles processed
     */
    public function bulkUpsert(array $articles, array $categoryPivot): int;


    /**
     * Get all sources.
     *
     * @return array
     */
    public function getSources(): array;
}
