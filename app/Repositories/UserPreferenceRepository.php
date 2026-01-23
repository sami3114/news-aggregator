<?php

namespace App\Repositories;

use App\Contracts\UserPreferenceRepositoryInterface;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\UserPreference;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class UserPreferenceRepository implements UserPreferenceRepositoryInterface
{

    protected UserPreference $model;
    public function __construct(UserPreference $model)
    {
        $this->model = $model;
    }

    /**
     * Get articles based on user preferences
     */
    public function getByPreferences(array $preferences, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('pagination.per_page');

        $sources = $preferences['preferred_sources'] ?? [];
        $categoryIds = $this->normalizeCategoryPreferences($preferences['preferred_categories'] ?? []);
        $authorIds = $this->normalizeAuthorPreferences($preferences['preferred_authors'] ?? []);

        $query = Article::query()->with(['author', 'categories']);

        // If no preferences set, return all articles
        if (empty($sources) && empty($categoryIds) && empty($authorIds)) {
            return $query->orderBy('published_at', 'desc')->paginate($perPage);
        }

        $query->where(function ($q) use ($sources, $categoryIds, $authorIds) {
            $q->when(!empty($sources), fn($query) => $query->whereIn('source', $sources))
                ->when(!empty($categoryIds), fn($query) => $query->orWhereHas('categories',
                    fn($cq) => $cq->whereIn('categories.id', $categoryIds)
                ), fn($query) => !empty($sources) ? $query : $query)
                ->when(!empty($authorIds), fn($query) => $query->orWhereIn('author_id', $authorIds));
        });

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    /**
     * Normalize category preferences
     */
    protected function normalizeCategoryPreferences(array $categories): array
    {
        if (empty($categories)) return [];

        $allIntegers = collect($categories)->every(fn($item) => is_int($item) || ctype_digit((string) $item));

        return $allIntegers
            ? array_map('intval', $categories)
            : Category::where(fn($q) => $q->whereIn('slug', array_map(fn($c) => Str::slug($c), $categories))
                ->orWhereIn('name', $categories))
                ->pluck('id')
                ->toArray();
    }

    /**
     * Normalize author preferences
     */
    protected function normalizeAuthorPreferences(array $authors): array
    {
        if (empty($authors)) return [];

        $allIntegers = collect($authors)->every(fn($item) => is_int($item) || ctype_digit((string) $item));

        return $allIntegers
            ? array_map('intval', $authors)
            : Author::where(fn($q) => $q->whereIn('slug', array_map(fn($a) => Str::slug($a), $authors))
                ->orWhereIn('name', $authors))
                ->pluck('id')
                ->toArray();
    }

    public function findByUserId(int $userId): ?UserPreference
    {
        return $this->model->byUser($userId)->first();
    }

    public function updateOrCreatePreferences(int $userId, array $data): UserPreference
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            [
                'preferred_sources'    => $data['preferred_sources'] ?? [],
                'preferred_categories' => $data['preferred_categories'] ?? [],
                'preferred_authors'    => $data['preferred_authors'] ?? [],
            ]
        );
    }
}
