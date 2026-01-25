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

    public function bulkInsert(array $articles): int
    {
        $now = now();
        $authors = [];
        $categories = [];

        foreach ($articles as $article) {
            if ($article['author_name'] ?? null) {
                $slug = Str::slug($article['author_name']);
                $authors[$slug] = [
                    'name' => $article['author_name'],
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($article['categories'] ?? [] as $cat) {
                $slug = Str::slug($cat);
                $categories[$slug] = [
                    'name' => $cat,
                    'slug' => $slug,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $authorMap = $this->authorRepository->upsertAndMap(array_values($authors));
        $categoryMap = $this->categoryRepository->upsertAndMap(array_values($categories));

        return DB::transaction(function () use ($authorMap, $categoryMap, $articles, $now) {
            $preparedArticles = array_map(function ($article) use ($authorMap, $now) {
                $authorSlug = isset($article['author_name']) ? Str::slug($article['author_name']) : null;

                return [
                    'external_id'  => $article['external_id'],
                    'source'       => $article['source'],
                    'source_name'  => $article['source_name'] ?? null,
                    'author_id'    => $authorSlug ? ($authorMap[$authorSlug] ?? null) : null,
                    'title'        => $article['title'],
                    'description'  => $article['description'] ?? null,
                    'content'      => $article['content'] ?? null,
                    'url'          => $article['url'],
                    'image_url'    => $article['image_url'] ?? null,
                    'published_at' => $article['published_at'] ?? $now,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }, $articles);

            Article::upsert(
                $preparedArticles,
                ['external_id', 'source'],
                ['source_name', 'author_id', 'title', 'description', 'content', 'url', 'image_url', 'published_at', 'updated_at']
            );

            $articleIds = Article::whereIn('external_id', array_column($articles, 'external_id'))
                ->pluck('id', 'external_id');

            $articleCategories = [];
            foreach ($articles as $article) {
                foreach ($article['categories'] ?? [] as $cat) {
                    $slug = Str::slug($cat);
                    if (isset($articleIds[$article['external_id']], $categoryMap[$slug])) {
                        $articleCategories[] = [
                            'article_id'  => $articleIds[$article['external_id']],
                            'category_id' => $categoryMap[$slug],
                        ];
                    }
                }
            }

            return $this->articleRepository->bulkUpsert($preparedArticles, $articleCategories);
        });
    }
}
