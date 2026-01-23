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
            'author_name' => $this->cleanByline($rawArticle['byline'] ?? null),
            'title' => $rawArticle['title'] ?? '',
            'description' => $rawArticle['abstract'] ?? null,
            'content' => $rawArticle['abstract'] ?? null,
            'url' => $rawArticle['url'] ?? '',
            'image_url' => $this->extractImageUrl($rawArticle),
            'categories' => [$this->mapCategory($rawArticle['section'] ?? 'news')],
            'published_at' => isset($rawArticle['published_date'])
                ? date('Y-m-d H:i:s', strtotime($rawArticle['published_date']))
                : now(),
        ];
    }

    /**
     * Clean byline text (remove "By " prefix)
     */
    protected function cleanByline(?string $byline): ?string
    {
        if (!$byline) {
            return null;
        }

        return preg_replace('/^By\s+/i', '', $byline);
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

        foreach ($multimedia as $media) {
            if (isset($media['format']) && $media['format'] === 'Large Thumbnail') {
                return $media['url'] ?? null;
            }
        }

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

    /**
     * Fetch articles by section
     *
     * @param string $section
     * @return array
     */
    public function fetchBySection(string $section): array
    {
        $endpoint = "topstories/v2/{$section}.json";
        $response = $this->makeRequest($endpoint, $this->buildParams([]));

        if (!$response) {
            return [];
        }

        $articles = $this->extractArticles($response);

        return array_map(fn($article) => $this->transformArticle($article), $articles);
    }

    /**
     * Transform article search result
     */
    protected function transformSearchResult(array $doc): array
    {
        $multimedia = $doc['multimedia'] ?? [];
        $imageUrl = null;

        if (!empty($multimedia)) {
            foreach ($multimedia as $media) {
                if (isset($media['type']) && $media['type'] === 'image') {
                    $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                    break;
                }
            }
        }

        return [
            'external_id' => $doc['_id'] ?? md5(Str::random(32)),
            'source' => $this->getSourceName(),
            'source_name' => 'The New York Times',
            'author_name' => $this->cleanByline($doc['byline']['original'] ?? null),
            'title' => $doc['headline']['main'] ?? '',
            'description' => $doc['abstract'] ?? $doc['lead_paragraph'] ?? null,
            'content' => $doc['lead_paragraph'] ?? null,
            'url' => $doc['web_url'] ?? '',
            'image_url' => $imageUrl,
            'categories' => [$this->mapCategory($doc['section_name'] ?? 'news')],
            'published_at' => isset($doc['pub_date'])
                ? date('Y-m-d H:i:s', strtotime($doc['pub_date']))
                : now(),
        ];
    }
}
