<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Str;

class NewsApiService extends BaseNewsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.news.newsapi.base_url');
        $this->apiKey = config('services.news.newsapi.api_key');
    }

    protected function getEndpoint(): string
    {
        return 'top-headlines';
    }

    protected function buildParams(array $params): array
    {
        $requestParams = [
            'apiKey' => $this->apiKey,
            'language' => $params['language'] ?? 'en',
            'pageSize' => $params['pageSize'] ?? 100,
        ];

        if (!empty($params['category'])) {
            $requestParams['category'] = $params['category'];
        }

        if (!empty($params['country'])) {
            $requestParams['country'] = $params['country'];
        } else {
            $requestParams['country'] = 'us';
        }

        if (!empty($params['q'])) {
            $requestParams['q'] = $params['q'];
        }

        return $requestParams;
    }

    protected function extractArticles(array $response): array
    {
        return $response['articles'] ?? [];
    }

    public function getSourceName(): string
    {
        return 'newsapi';
    }

    public function transformArticle(array $rawArticle): array
    {
        return [
            'external_id' => md5($rawArticle['url'] ?? Str::random(32)),
            'source' => $this->getSourceName(),
            'source_name' => $rawArticle['source']['name'] ?? 'NewsAPI',
            'author' => $rawArticle['author'] ?? null,
            'title' => $rawArticle['title'] ?? '',
            'description' => $rawArticle['description'] ?? null,
            'content' => $rawArticle['content'] ?? null,
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $rawArticle['urlToImage'] ?? null,
            'category' => $rawArticle['category'] ?? 'general',
            'published_at' => isset($rawArticle['publishedAt']) ? date('Y-m-d H:i:s', strtotime($rawArticle['publishedAt'])) : now(),
        ];
    }

    /**
     * Fetch articles by category.
     *
     * @param string $category
     * @return array
     */
    public function fetchByCategory(string $category): array
    {
        return $this->fetchArticles(['category' => $category]);
    }
}
