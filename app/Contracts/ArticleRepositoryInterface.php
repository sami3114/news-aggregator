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
     * @param array $articlesData
     * @return int Number of articles processed
     */
    public function bulkUpsert(array $articlesData): int;

    /**
     * Search articles
     *
     * @param string $query
     * @param array $filters
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], ?int $perPage = null): LengthAwarePaginator;

    /**
     * Get articles by user preferences
     *
     * @param array $preferences
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function getByPreferences(array $preferences, ?int $perPage = null): LengthAwarePaginator;

    /**
     * Get all categories.
     *
     * @return array
     */
    public function getCategories(): array;

    /**
     * Get all sources.
     *
     * @return array
     */
    public function getSources(): array;

    /**
     * Get all authors.
     *
     * @return array
     */
    public function getAuthors(): array;

}
