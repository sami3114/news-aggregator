<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Str;

class NewYorkTimesService extends BaseNewsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.news.nyt.base_url');
        $this->apiKey = config('services.news.nyt.api_key');
    }

    protected function getEndpoint(): string
    {
        return 'topstories/v2/home.json';
    }

    protected function buildParams(array $params): array
    {
        return [
            'api-key' => $this->apiKey,
        ];
    }

    protected function extractArticles(array $response): array
    {
        return $response['results'] ?? [];
    }

    public function getSourceName(): string
    {
        return 'nytimes';
    }

    public function transformArticle(array $rawArticle): array
    {
        return [
            'external_id' => $rawArticle['uri'] ?? md5($rawArticle['url'] ?? Str::random(32)),
            'source' => $this->getSourceName(),
            'source_name' => 'The New York Times',
            'author' => $rawArticle['byline'] ?? null,
            'title' => $rawArticle['title'] ?? '',
            'description' => $rawArticle['abstract'] ?? null,
            'content' => $rawArticle['abstract'] ?? null,
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $this->extractImageUrl($rawArticle),
            'category' => $this->mapCategory($rawArticle['section'] ?? 'news'),
            'published_at' => isset($rawArticle['published_date'])
                ? date('Y-m-d H:i:s', strtotime($rawArticle['published_date']))
                : now(),
        ];
    }

    /**
     * Extract the best image URL from multimedia array
     */
    protected function extractImageUrl(array $article): ?string
    {
        $multimedia = $article['multimedia'] ?? [];

        if (empty($multimedia)) {
            return null;
        }

        // Try to find a medium-sized image
        foreach ($multimedia as $media) {
            if (isset($media['format']) && $media['format'] === 'Large Thumbnail') {
                return $media['url'] ?? null;
            }
        }

        // Fall back to first image
        return $multimedia[0]['url'] ?? null;
    }

    /**
     * Map NYT section to standard category
     */
    protected function mapCategory(string $section): string
    {
        $section = strtolower($section);

        $categoryMap = [
            'world' => 'world',
            'u.s.' => 'national',
            'us' => 'national',
            'politics' => 'politics',
            'business' => 'business',
            'technology' => 'technology',
            'science' => 'science',
            'climate' => 'environment',
            'sports' => 'sports',
            'arts' => 'entertainment',
            'movies' => 'entertainment',
            'theater' => 'entertainment',
            'books' => 'entertainment',
            'style' => 'lifestyle',
            'food' => 'lifestyle',
            'travel' => 'travel',
            'magazine' => 'magazine',
            'opinion' => 'opinion',
            'health' => 'health',
            'realestate' => 'realestate',
            'automobiles' => 'automobiles',
            'obituaries' => 'obituaries',
            'upshot' => 'analysis',
            'nyregion' => 'local',
        ];

        return $categoryMap[$section] ?? 'general';
    }
}
