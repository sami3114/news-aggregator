<?php

namespace App\Services;

use App\Contracts\ArticleRepositoryInterface;
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

    public function __construct(protected ArticleRepositoryInterface $articleRepository)
    {
        $this->registerProviders();
    }

    /**
     * Register all news providers
     */
    protected function registerProviders(): void
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
                $articles = $this->fetchFromProvider($provider);
                $count = $this->storeArticles($articles);

                $results['success'][$name] = $count;
                $results['total_articles'] += $count;

                Log::info("Fetched {$count} articles from {$name}");
            } catch (\Exception $e) {
                $results['failed'][$name] = $e->getMessage();
                Log::error("Failed to fetch from {$name}: " . $e->getMessage());
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

        $articles = $this->fetchFromProvider($provider);
        return $this->storeArticles($articles);
    }

    /**
     * Fetch articles from a provider
     */
    protected function fetchFromProvider(NewsServiceInterface $provider): array
    {
        $articles = [];

        $articles = array_merge($articles, $provider->fetchArticles());

        if ($provider instanceof NewsApiService) {
            foreach (self::CATEGORIES as $category) {
                $categoryArticles = $provider->fetchByCategory($category);
                foreach ($categoryArticles as &$article) {
                    $article['category'] = $category;
                }
                $articles = array_merge($articles, $categoryArticles);
            }
        }

        if ($provider instanceof GuardianService) {
            foreach (self::SECTIONS as $section) {
                $articles = array_merge($articles, $provider->fetchBySection($section));
            }
        }

        return $articles;
    }

    /**
     * Store articles in the database
     */
    protected function storeArticles(array $articles): int
    {
        $stored = 0;

        foreach ($articles as $articleData) {
            try {
                // Skip if no title
                if (empty($articleData['title']) || $articleData['title'] === '[Removed]') {
                    continue;
                }

                $this->articleRepository->createOrUpdate($articleData);
                $stored++;
            } catch (\Exception $e) {
                Log::warning("Failed to store article: " . $e->getMessage(), [
                    'title' => $articleData['title'] ?? 'unknown',
                ]);
            }
        }

        return $stored;
    }

    /**
     * Search articles across all providers (live)
     */
    public function searchLive(string $query): array
    {
        $results = [];

        foreach ($this->providers as $name => $provider) {
            try {
                if (method_exists($provider, 'searchArticles')) {
                    $articles = $provider->searchArticles($query);
                    $results[$name] = $articles;
                }
            } catch (\Exception $e) {
                Log::error("Search failed for {$name}: " . $e->getMessage());
            }
        }

        return $results;
    }
}
