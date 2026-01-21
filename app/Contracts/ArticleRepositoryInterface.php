<?php

namespace App\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    /**
     * Get all articles.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 20):LengthAwarePaginator;

    /**
     * Find article by id.
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id);

    /**
     * Create or update article.
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data);

    /**
     * Search articles.
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get articles by preferences.
     *
     * @param array $preferences
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPreferences(array $preferences, int $perPage = 15): LengthAwarePaginator;

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
