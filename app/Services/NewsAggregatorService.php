<?php

namespace App\Services;

use App\Contracts\NewsServiceInterface;
use App\Services\NewsProviders\GuardianService;
use App\Services\NewsProviders\NewsApiService;
use App\Services\NewsProviders\NewYorkTimesService;
use Illuminate\Support\Facades\Log;

class NewsAggregatorService
{
    private const CATEGORIES = ['business', 'technology', 'science', 'health', 'sports', 'entertainment'];
    private const SECTIONS = ['world', 'business', 'technology', 'science', 'sport', 'culture'];

    protected array $providers;

    public function __construct(protected ArticleService $articleService)
    {
        $this->providers = [
            'newsapi' => new NewsApiService(),
            'guardian' => new GuardianService(),
            'nytimes' => new NewYorkTimesService(),
        ];
    }

    public function fetchAllArticles(): array
    {
        $results = ['success' => [], 'failed' => [], 'total_articles' => 0];

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

    public function fetchFromSource(string $sourceName): int
    {
        if (!isset($this->providers[$sourceName])) {
            throw new \InvalidArgumentException("Unknown news source: {$sourceName}");
        }

        return $this->fetchFromProvider($this->providers[$sourceName]);
    }

    public function getAvailableSources(): array
    {
        return array_keys($this->providers);
    }

    protected function fetchFromProvider(NewsServiceInterface $provider): int
    {
        $articles = $provider->fetchArticles();

        if ($provider instanceof NewsApiService) {
            foreach (self::CATEGORIES as $category) {
                $articles = array_merge($articles, $provider->fetchByCategory($category));
            }
        } elseif ($provider instanceof GuardianService || $provider instanceof NewYorkTimesService) {
            foreach (self::SECTIONS as $section) {
                $articles = array_merge($articles, $provider->fetchBySection($section));
            }
        }

        return $this->articleService->bulkInsert($articles);
    }
}
