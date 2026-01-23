<?php

namespace App\Contracts;

interface AuthorRepositoryInterface
{
    /**
     * Get all authors.
     *
     * @return array
     */
    public function getAuthors(): array;

    /**
     * Bulk upsert articles with authors
     *
     * @param array $preparedAuthors
     * @return array
     */
    public function upsertAndMap(array $preparedAuthors): array;
}
