<?php

namespace App\Contracts;

interface CategoryRepositoryInterface
{
    /**
     * Get all categories.
     *
     * @return array
     */
    public function getCategories(): array;

    /**
     * Bulk upsert articles with categories
     *
     * @param array $preparedCategories
     * @return array
     */
    public function upsertAndMap(array $preparedCategories): array;
}
