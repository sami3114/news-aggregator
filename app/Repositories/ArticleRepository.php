<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected Article $model)
    {}

    /**
     * Get all articles with optional filters & search
     */
    public function getAll(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('pagination.per_page');

        $query = $this->model->newQuery()->with(['author', 'categories']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('published_at', 'desc')->paginate($perPage);
    }

    /**
     * Bulk upsert articles with authors and categories
     *
     * @param array $articles
     * @param array $categoryPivot
     * @return int
     */
    public function bulkUpsert(array $articles, array $categoryPivot): int
    {
        Article::upsert(
            $articles,
            ['external_id', 'source'],
            [
                'source_name',
                'author_id',
                'title',
                'description',
                'content',
                'url',
                'image_url',
                'published_at',
                'updated_at'
            ]
        );

        if (!empty($categoryPivot)) {
            DB::table('article_category')->upsert(
                $categoryPivot,
                ['article_id', 'category_id']
            );
        }

        return count($articles);
    }

    /**
     * Get all unique sources
     */
    public function getSources(): array
    {
        return $this->model
            ->select('source', 'source_name')
            ->distinct()
            ->get()
            ->map(fn($item) => [
                'id' => $item->source,
                'name' => $item->source_name,
            ])
            ->toArray();
    }

    /**
     * Apply filters to query using when()
     */
    protected function applyFilters($query, array $filters): void
    {
        $query->when(!empty($filters['q']), function ($q) use ($filters) {
            $q->search($filters['q']);
        });

        $query->when(!empty($filters['keyword']), function ($q) use ($filters) {
            $q->search($filters['keyword']);
        });

        $query->when(!empty($filters['source']), function ($q) use ($filters) {
            $q->bySource($filters['source']);
        });

        $query->when(!empty($filters['category']), function ($q) use ($filters) {
            $q->byCategorySlug($filters['category']);
        });

        $query->when(!empty($filters['author_id']), function ($q) use ($filters) {
            $q->byAuthor($filters['author_id']);
        });

        $query->when(!empty($filters['from_date']) || !empty($filters['to_date']), function ($q) use ($filters) {
            $q->betweenDates(
                $filters['from_date'] ?? null,
                $filters['to_date'] ?? null
            );
        });
    }
}
