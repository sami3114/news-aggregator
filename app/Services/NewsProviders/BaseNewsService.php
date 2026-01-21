<?php

namespace App\Services\NewsProviders;

use App\Contracts\NewsServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseNewsService implements NewsServiceInterface
{
    /**
     * Base URL of the news source.
     * @var string
     */
    protected string $baseUrl;

    /**
     * API key for the news source.
     *
     * @var string
     */
    protected string $apiKey;
    /**
     * Make a request to the news source API.
     *
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    protected function makeRequest(string $endpoint, array $params = []): ?array
    {
        try {
            $response = Http::timeout(30)
                ->connectTimeout(5)
                ->retry(2, 500)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("News API Error [{$this->getSourceName()}]", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("News API Exception [{$this->getSourceName()}]", [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch and transform articles
     *
     * @param array $params
     * @return array
     */
    public function fetchArticles(array $params = []): array
    {
        $response = $this->makeRequest($this->getEndpoint(), $this->buildParams($params));

        if (!$response) {
            return [];
        }

        $articles = $this->extractArticles($response);

        return array_map(fn($article) => $this->transformArticle($article), $articles);
    }

    /**
     * Get API endpoint
     *
     * @return string
     */
    abstract protected function getEndpoint(): string;

    /**
     * Build params for the request
     *
     * @param array $params
     * @return array
     */
    abstract protected function buildParams(array $params): array;

    /**
     * Extract articles from the api response
     *
     * @param array $response
     * @return array
     */
    abstract protected function extractArticles(array $response): array;

}
