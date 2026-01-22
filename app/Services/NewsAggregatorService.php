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
                $articles = array_merge($articles, $categoryArticles);
            }
        }

        if ($provider instanceof GuardianService) {
            foreach (self::SECTIONS as $section) {
                $articles = array_merge($articles, $provider->fetchBySection($section));
            }
        }

        if ($provider instanceof NewYorkTimesService) {
            foreach (self::SECTIONS as $section) {
                $articles = array_merge($articles, $provider->fetchBySection($section));
            }
        }

        return $articles;
    }

    /**
     * Store articles in the database using bulk upsert
     */
    protected function storeArticles(array $articles): int
    {
        $validArticles = array_filter($articles, function ($article) {
            return !empty($article['title']) && $article['title'] !== '[Removed]';
        });

        if (empty($validArticles)) {
            return 0;
        }

        try {
            return $this->articleRepository->bulkUpsert(array_values($validArticles));
        } catch (\Exception $e) {
            Log::error("Failed to bulk upsert articles: " . $e->getMessage());
            return 0;
        }
    }

}
