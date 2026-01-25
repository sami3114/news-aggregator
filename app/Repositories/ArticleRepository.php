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
        $query = $this->model->with(['author', 'categories']);

        $query->when($filters['q'] ?? $filters['keyword'] ?? null, fn($q, $search) => $q->search($search))
            ->when($filters['source'] ?? null, fn($q, $source) => $q->bySource($source))
            ->when($filters['category'] ?? null, fn($q, $category) => $q->byCategorySlug($category))
            ->when($filters['author_id'] ?? null, fn($q, $author) => $q->byAuthor($author))
            ->when($filters['from_date'] ?? $filters['to_date'] ?? null,
                fn($q) => $q->betweenDates($filters['from_date'] ?? null, $filters['to_date'] ?? null));

        return $query->orderBy('published_at', 'desc')
            ->paginate($perPage ?? config('pagination.per_page'));
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

        if ($categoryPivot) {
            DB::table('article_category')->upsert($categoryPivot, ['article_id', 'category_id']);
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
}
