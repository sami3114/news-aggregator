<?php

namespace App\Repositories;

use App\Contracts\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Get all unique categories
     */
    public function getCategories(): array
    {
        return Category::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function upsertAndMap(array $preparedCategories): array
    {
        if (empty($preparedCategories)) {
            return [];
        }

        Category::upsert($preparedCategories, ['slug'], ['name', 'updated_at']);

        return Category::whereIn('slug', array_column($preparedCategories, 'slug'))
            ->pluck('id', 'slug')
            ->toArray();
    }
}
