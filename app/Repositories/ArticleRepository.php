<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\AuthorRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        protected Article $model,
        protected AuthorRepositoryInterface $authorRepository,
        protected CategoryRepositoryInterface $categoryRepository
    ){}

    /**
     * Get all articles with optional filters
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
     * @return int Number of articles processed
     */
    public function bulkUpsert(array $articles): int
    {
        if (empty($articles)) return 0;

        $articles = $this->deduplicateArticles($articles);

        $now = now();

        $authors = [];
        $categories = [];
        $preparedArticles = [];

        foreach ($articles as $article)
        {
            $authorSlug = null;
            if (!empty($article['author_name'])) {
                $authorSlug = Str::slug($article['author_name']);
                $authors[$authorSlug] = [
                    'name' => $article['author_name'],
                    'slug' => $authorSlug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($article['categories'] ?? [] as $cat) {
                $slug = Str::slug($cat);
                $categories[$slug] = [
                    'name' => ucfirst($cat),
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $preparedArticles[] = [
                'external_id'  => $article['external_id'],
                'source'       => $article['source'],
                'source_name'  => $article['source_name'] ?? null,
                'author_slug'  => $authorSlug,
                'title'        => $article['title'],
                'description'  => $article['description'] ?? null,
                'content'      => $article['content'] ?? null,
                'url'          => $article['url'],
                'image_url'    => $article['image_url'] ?? null,
                'published_at' => $article['published_at'] ?? $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        return DB::transaction(function () use ($authors, $categories, $preparedArticles, $articles) {

            // Authors
            $authorMap = $this->authorRepository->upsertAndMap(array_values($authors));

            // Categories
            $categoryMap = $this->categoryRepository->upsertAndMap(array_values($categories));

            // Final article payload
            $finalArticles = collect($preparedArticles)->map(function ($a) use ($authorMap) {
                return [
                    'external_id'  => $a['external_id'],
                    'source'       => $a['source'],
                    'source_name'  => $a['source_name'],
                    'author_id'    => $a['author_slug'] ? ($authorMap[$a['author_slug']] ?? null) : null,
                    'title'        => $a['title'],
                    'description'  => $a['description'],
                    'content'      => $a['content'],
                    'url'          => $a['url'],
                    'image_url'    => $a['image_url'],
                    'published_at' => $a['published_at'],
                    'created_at'   => $a['created_at'],
                    'updated_at'   => $a['updated_at'],
                ];
            })->toArray();

            Article::upsert(
                $finalArticles,
                ['external_id', 'source'],
                ['source_name','author_id','title','description','content','url','image_url','published_at','updated_at']
            );

            $this->syncCategories($articles, $categoryMap);

            return count($articles);
        });
    }

    /**
     * Sync categories for articles
     *
     * @param array $articles
     * @param array $categoryMap
     * @return void
     */
    protected function syncCategories(array $articles, array $categoryMap): void
    {
        if (empty($categoryMap)) return;

        $articleIds = Article::whereIn('external_id', array_column($articles, 'external_id'))
            ->pluck('id', 'external_id');

        $pivot = [];

        foreach ($articles as $article) {
            foreach ($article['categories'] ?? [] as $cat) {
                $slug = Str::slug($cat);
                if (isset($categoryMap[$slug], $articleIds[$article['external_id']])) {
                    $pivot[] = [
                        'article_id'  => $articleIds[$article['external_id']],
                        'category_id' => $categoryMap[$slug],
                    ];
                }
            }
        }

        DB::table('article_category')->upsert(
            $pivot,
            ['article_id', 'category_id']
        );
    }
    /**
     * Deduplicate articles by external_id, merging categories
     */
    protected function deduplicateArticles(array $articles): array
    {
        $unique = [];

        foreach ($articles as $article) {
            $key = $article['external_id'].'|'.$article['source'];

            if (!isset($unique[$key])) {
                $unique[$key] = $article;
            } else {
                $unique[$key]['categories'] = array_unique(array_merge(
                    $unique[$key]['categories'] ?? [],
                    $article['categories'] ?? []
                ));
            }
        }

        return array_values($unique);
    }

    /**
     * Search articles by keyword
     */
    public function search(string $query, array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('pagination.per_page');

        $articleQuery = $this->model->newQuery()->with(['author', 'categories']);

        $articleQuery->search($query);
        $this->applyFilters($articleQuery, $filters);

        return $articleQuery->orderBy('published_at', 'desc')->paginate($perPage);
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
