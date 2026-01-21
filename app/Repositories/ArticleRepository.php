<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected Article $model)
    {
        //
    }

    /**
     * Get all articles with optional filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $this->applyFilters($query, $filters);

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    /**
     * Find article by id.
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create or update article.
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data)
    {
        return $this->model->updateOrCreate(
            [
                'external_id' => $data['external_id'],
                'source' => $data['source'],
            ],
            $data
        );
    }

    /**
     * Search articles by query.
     *
     * @param string $query
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $articleQuery = $this->model->newQuery();

        $articleQuery->search($query);
        $this->applyFilters($articleQuery, $filters);

        return $articleQuery->orderBy('published_at', 'desc')->paginate($perPage);
    }

    /**
     * Get articles based on user preferences
     *
     * @param array $preferences
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPreferences(array $preferences, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($preferences['preferred_sources'])) {
            $query->whereIn('source', $preferences['preferred_sources']);
        }

        if (!empty($preferences['preferred_categories'])) {
            $query->whereIn('category', $preferences['preferred_categories']);
        }

        if (!empty($preferences['preferred_authors'])) {
            $query->where(function ($q) use ($preferences) {
                foreach ($preferences['preferred_authors'] as $author) {
                    $q->orWhere('author', 'like', "%{$author}%");
                }
            });
        }

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    /**
     * Get distinct categories from articles.
     *
     * @return array
     */
    public function getCategories(): array
    {
        return Cache::remember('article_categories', 3600, function () {
            return $this->model
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->pluck('category')
                ->toArray();
        });
    }

    /**
     * Get distinct sources from articles.
     *
     * @return array
     */
    public function getSources(): array
    {
        return Cache::remember('article_sources', 3600, function () {
            return $this->model
                ->select('source', 'source_name')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->source,
                        'name' => $item->source_name,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get distinct authors from articles.
     *
     * @return array
     */
    public function getAuthors(): array
    {
        return Cache::remember('article_authors', 3600, function () {
            return $this->model
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->distinct()
                ->pluck('author')
                ->toArray();
        });
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['keyword'])) {
            $query->search($filters['keyword']);
        }

        if (!empty($filters['source'])) {
            $query->bySource($filters['source']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['author'])) {
            $query->byAuthor($filters['author']);
        }

        if (!empty($filters['from_date']) || !empty($filters['to_date'])) {
            $query->betweenDates(
                $filters['from_date'] ?? null,
                $filters['to_date'] ?? null
            );
        }
    }
}
