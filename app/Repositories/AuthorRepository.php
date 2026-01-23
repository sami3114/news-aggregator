<?php

namespace App\Repositories;

use App\Contracts\AuthorRepositoryInterface;
use App\Models\Author;

class AuthorRepository implements AuthorRepositoryInterface
{
    /**
     * Get all unique authors
     */
    public function getAuthors(): array
    {
        return Author::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function upsertAndMap(array $preparedAuthors): array
    {
       if (empty($preparedAuthors)) {
           return [];
       }

        Author::upsert($preparedAuthors, ['slug'],  ['name', 'updated_at']);

       return Author::whereIn('slug', array_column($preparedAuthors, 'slug'))
        ->pluck('id', 'slug')
        ->toArray();
    }
}
