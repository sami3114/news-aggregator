<?php

namespace App\Repositories;

use App\Contracts\ArticleRepositoryInterface;
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
    public function __construct(protected Article $model)
    {
        //
    }

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
     * @param array $articlesData
     * @return int Number of articles processed
     */
    public function bulkUpsert(array $articlesData): int
    {
        if (empty($articlesData)) {
            return 0;
        }

        // Step 0: Deduplicate articles by external_id (keep last occurrence which has more categories)
        $articlesData = $this->deduplicateArticles($articlesData);

        return DB::transaction(function () use ($articlesData) {
            // Step 1: Extract and upsert all unique authors
            $authorMap = $this->upsertAuthors($articlesData);

            // Step 2: Extract and upsert all unique categories
            $categoryMap = $this->upsertCategories($articlesData);

            // Step 3: Prepare articles data with author_id
            $preparedArticles = $this->prepareArticlesForUpsert($articlesData, $authorMap);

            // Step 4: Upsert articles
            $this->model->upsert(
                $preparedArticles,
                ['external_id', 'source'], // Unique columns
                ['source_name', 'author_id', 'title', 'description', 'content', 'url', 'image_url', 'published_at', 'updated_at'] // Columns to update
            );

            // Step 5: Sync categories for each article
            $this->syncArticleCategories($articlesData, $categoryMap);

            return count($articlesData);
        });
    }

    /**
     * Deduplicate articles by external_id, merging categories
     */
    protected function deduplicateArticles(array $articlesData): array
    {
        $unique = [];

        foreach ($articlesData as $article) {
            $key = $article['external_id'] . '|' . $article['source'];

            if (isset($unique[$key])) {
                // Merge categories from duplicate articles
                $existingCategories = (array) ($unique[$key]['categories'] ?? []);
                $newCategories = (array) ($article['categories'] ?? []);
                $unique[$key]['categories'] = array_unique(array_merge($existingCategories, $newCategories));
            } else {
                $unique[$key] = $article;
            }
        }

        return array_values($unique);
    }

    /**
     * Upsert authors and return slug => id map
     */
    protected function upsertAuthors(array $articlesData): array
    {
        // Extract unique author names
        $authorNames = collect($articlesData)
            ->pluck('author_name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($authorNames)) {
            return [];
        }

        // Prepare authors for upsert
        $authorsToUpsert = array_map(fn($name) => [
            'name' => $name,
            'slug' => Str::slug($name),
            'created_at' => now(),
            'updated_at' => now(),
        ], $authorNames);

        // Upsert authors
        Author::upsert($authorsToUpsert, ['slug'], ['name', 'updated_at']);

        // Get author map (slug => id)
        return Author::whereIn('slug', array_column($authorsToUpsert, 'slug'))
            ->pluck('id', 'slug')
            ->toArray();
    }

    /**
     * Upsert categories and return slug => id map
     */
    protected function upsertCategories(array $articlesData): array
    {
        // Extract unique category names
        $categoryNames = collect($articlesData)
            ->pluck('categories')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($categoryNames)) {
            return [];
        }

        // Prepare categories for upsert
        $categoriesToUpsert = array_map(fn($name) => [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'created_at' => now(),
            'updated_at' => now(),
        ], $categoryNames);

        // Upsert categories
        Category::upsert($categoriesToUpsert, ['slug'], ['name', 'updated_at']);

        // Get category map (slug => id)
        return Category::whereIn('slug', array_column($categoriesToUpsert, 'slug'))
            ->pluck('id', 'slug')
            ->toArray();
    }

    /**
     * Prepare articles data for upsert
     */
    protected function prepareArticlesForUpsert(array $articlesData, array $authorMap): array
    {
        return array_map(function ($data) use ($authorMap) {
            $authorSlug = !empty($data['author_name']) ? Str::slug($data['author_name']) : null;

            return [
                'external_id' => $data['external_id'],
                'source' => $data['source'],
                'source_name' => $data['source_name'] ?? null,
                'author_id' => $authorSlug ? ($authorMap[$authorSlug] ?? null) : null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'content' => $data['content'] ?? null,
                'url' => $data['url'],
                'image_url' => $data['image_url'] ?? null,
                'published_at' => $data['published_at'] ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $articlesData);
    }

    /**
     * Sync categories for articles using insertOrIgnore to handle duplicates
     */
    protected function syncArticleCategories(array $articlesData, array $categoryMap): void
    {
        if (empty($categoryMap)) {
            return;
        }

        // Get all articles that were just upserted
        $externalIds = array_column($articlesData, 'external_id');
        $articles = $this->model->whereIn('external_id', $externalIds)->get(['id', 'external_id']);
        $articleMap = $articles->pluck('id', 'external_id')->toArray();

        // Get unique article IDs
        $articleIds = array_values($articleMap);

        // Delete existing relationships for these articles
        DB::table('article_category')
            ->whereIn('article_id', $articleIds)
            ->delete();

        // Prepare pivot data
        $pivotData = [];

        foreach ($articlesData as $data) {
            $articleId = $articleMap[$data['external_id']] ?? null;
            if (!$articleId || empty($data['categories'])) {
                continue;
            }

            foreach ((array) $data['categories'] as $categoryName) {
                $categorySlug = Str::slug($categoryName);
                $categoryId = $categoryMap[$categorySlug] ?? null;

                if ($categoryId) {
                    $pivotData[] = [
                        'article_id' => $articleId,
                        'category_id' => $categoryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($pivotData)) {
            // Use insertOrIgnore to silently skip duplicates
            DB::table('article_category')->insertOrIgnore($pivotData);
        }
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
     * Get articles based on user preferences
     */
    public function getByPreferences(array $preferences, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('pagination.per_page');

        $sources = $preferences['preferred_sources'] ?? [];
        $categoryIds = $this->normalizeCategoryPreferences($preferences['preferred_categories'] ?? []);
        $authorIds = $this->normalizeAuthorPreferences($preferences['preferred_authors'] ?? []);

        $query = $this->model->newQuery()->with(['author', 'categories']);

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
     * Normalize category preferences - convert slugs/names to IDs
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
     * Normalize author preferences - convert slugs/names to IDs
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
     * Get all unique authors
     */
    public function getAuthors(): array
    {
        return Author::select('id', 'name', 'slug')
            ->orderBy('name')
            ->get()
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
