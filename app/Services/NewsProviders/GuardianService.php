<?php

namespace App\Services\NewsProviders;

use Illuminate\Support\Str;

class GuardianService extends BaseNewsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->baseUrl = config('services.news.guardian.base_url');
        $this->apiKey = config('services.news.guardian.api_key');
    }

    protected function getEndpoint(): string
    {
        return 'search';
    }

    protected function buildParams(array $params): array
    {
        $requestParams = [
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,trailText,byline,thumbnail',
            'page-size' => $params['pageSize'] ?? 20,
            'order-by' => $params['orderBy'] ?? 'newest',
        ];

        if (!empty($params['q'])) {
            $requestParams['q'] = $params['q'];
        }

        if (!empty($params['section'])) {
            $requestParams['section'] = $params['section'];
        }

        if (!empty($params['from-date'])) {
            $requestParams['from-date'] = $params['from-date'];
        }

        if (!empty($params['to-date'])) {
            $requestParams['to-date'] = $params['to-date'];
        }

        return $requestParams;
    }

    protected function extractArticles(array $response): array
    {
        return $response['response']['results'] ?? [];
    }

    public function getSourceName(): string
    {
        return 'guardian';
    }

    public function transformArticle(array $rawArticle): array
    {
        $fields = $rawArticle['fields'] ?? [];

        return [
            'external_id' => $rawArticle['id'] ?? md5(Str::random(32)),
            'source' => $this->getSourceName(),
            'source_name' => 'The Guardian',
            'author_name' => $fields['byline'] ?? null,
            'title' => $fields['headline'] ?? $rawArticle['webTitle'] ?? '',
            'description' => $fields['trailText'] ?? null,
            'content' => $this->cleanContent($fields['body'] ?? null),
            'url' => $rawArticle['webUrl'] ?? '',
            'image_url' => $fields['thumbnail'] ?? null,
            'categories' => [$this->mapCategory($rawArticle['sectionId'] ?? 'news')],
            'published_at' => isset($rawArticle['webPublicationDate'])
                ? date('Y-m-d H:i:s', strtotime($rawArticle['webPublicationDate']))
                : now(),
        ];
    }

    /**
     * Clean HTML content
     */
    protected function cleanContent(?string $content): ?string
    {
        if (!$content) {
            return null;
        }

        return strip_tags($content);
    }

    /**
     * Map Guardian section to standard category
     */
    protected function mapCategory(string $section): string
    {
        $categoryMap = [
            'world' => 'world',
            'uk-news' => 'national',
            'us-news' => 'national',
            'politics' => 'politics',
            'business' => 'business',
            'technology' => 'technology',
            'science' => 'science',
            'environment' => 'environment',
            'sport' => 'sports',
            'football' => 'sports',
            'culture' => 'entertainment',
            'film' => 'entertainment',
            'music' => 'entertainment',
            'books' => 'entertainment',
            'lifeandstyle' => 'lifestyle',
            'fashion' => 'lifestyle',
            'food' => 'lifestyle',
            'travel' => 'travel',
            'money' => 'business',
            'education' => 'education',
            'society' => 'society',
            'media' => 'media',
            'law' => 'law',
            'commentisfree' => 'opinion',
        ];

        return $categoryMap[$section] ?? 'general';
    }

    /**
     * Fetch articles by section
     */
    public function fetchBySection(string $section): array
    {
        return $this->fetchArticles(['section' => $section]);
    }

    /**
     * Get available sections
     */
    public function getSections(): array
    {
        $response = $this->makeRequest('sections', ['api-key' => $this->apiKey]);

        if (!$response) {
            return [];
        }

        return $response['response']['results'] ?? [];
    }
}
