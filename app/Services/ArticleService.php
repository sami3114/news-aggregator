<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\AuthorRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        protected ArticleRepositoryInterface $articleRepository,
        protected AuthorRepositoryInterface $authorRepository,
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function store(array $articles): int
    {
        if (empty($articles)) return 0;

        $articles = $this->deduplicateArticles($articles);
        $now = now();

        $authors = [];
        $categories = [];
        $preparedArticles = [];
        $articleCategories = [];

        foreach ($articles as $article) {

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

        return DB::transaction(function () use ($authors, $categories, $preparedArticles, $articles, &$articleCategories)
        {
            $authorMap = $this->authorRepository->upsertAndMap(array_values($authors));

            $categoryMap = $this->categoryRepository->upsertAndMap(array_values($categories));

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

            $articleIds = Article::whereIn(
                'external_id',
                array_column($articles, 'external_id')
            )->pluck('id', 'external_id');

            foreach ($articles as $article) {
                foreach ($article['categories'] ?? [] as $cat) {
                    $slug = Str::slug($cat);
                    if (isset($articleIds[$article['external_id']], $categoryMap[$slug]))
                    {
                        $articleCategories[] = [
                            'article_id'  => $articleIds[$article['external_id']],
                            'category_id' => $categoryMap[$slug],
                        ];
                    }
                }
            }

            return $this->articleRepository->bulkUpsert(
                $finalArticles,
                $articleCategories
            );
        });
    }

    /**
     * DEDUPLICATION
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
}
