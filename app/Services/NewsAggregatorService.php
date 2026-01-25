<?php

namespace App\Services;

use App\Contracts\NewsServiceInterface;
use App\Services\NewsProviders\GuardianService;
use App\Services\NewsProviders\NewsApiService;
use App\Services\NewsProviders\NewYorkTimesService;
use Illuminate\Support\Facades\Log;

class NewsAggregatorService
{
    protected array $providers = [];

    private const CATEGORIES = [
        'business',
        'technology',
        'science',
        'health',
        'sports',
        'entertainment',
    ];

    public const SECTIONS = [
        'world',
        'business',
        'technology',
        'science',
        'sport',
        'culture',
    ];

    public function __construct(protected ArticleService $articleService)
    {
        $this->providers = [
            'newsapi' => new NewsApiService(),
            'guardian' => new GuardianService(),
            'nytimes' => new NewYorkTimesService(),
        ];
    }

    /**
     * Get a specific provider
     */
    public function getProvider(string $name): ?NewsServiceInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Get all registered providers
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Fetch articles from all sources and store them
     */
    public function fetchAllArticles(): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'total_articles' => 0,
        ];

        foreach ($this->providers as $name => $provider) {
            try {
                $count = $this->fetchFromProvider($provider);
                $results['success'][$name] = $count;
                $results['total_articles'] += $count;
                Log::info("Fetched {$count} articles from {$name}");
            } catch (\Exception $e) {
                $results['failed'][$name] = $e->getMessage();
                Log::error("Failed to fetch from {$name}: {$e->getMessage()}");
            }
        }

        return $results;
    }

    /**
     * Fetch articles from a specific source
     */
    public function fetchFromSource(string $sourceName): int
    {
        $provider = $this->getProvider($sourceName);

        if (!$provider) {
            throw new \InvalidArgumentException("Unknown news source: {$sourceName}");
        }

        return $this->fetchFromProvider($provider);
    }

    protected function fetchFromProvider(NewsServiceInterface $provider): int
    {
        $articles = $provider->fetchArticles();

        if ($provider instanceof NewsApiService) {
            foreach (self::CATEGORIES as $category) {
                $articles = array_merge($articles, $provider->fetchByCategory($category));
            }
        }

        if ($provider instanceof GuardianService || $provider instanceof NewYorkTimesService) {
            foreach (self::SECTIONS as $section) {
                $articles = array_merge($articles, $provider->fetchBySection($section));
            }
        }

        return $this->articleService->bulkInsert($articles);
    }
}
