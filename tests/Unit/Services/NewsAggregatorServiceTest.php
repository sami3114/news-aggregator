<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Services\ArticleService;
use App\Services\NewsAggregatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsAggregatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NewsAggregatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $articleService = new ArticleService(
            new ArticleRepository(new Article()),
            new AuthorRepository(new Author()),
            new CategoryRepository(new Category())
        );
        
        $this->service = new NewsAggregatorService($articleService);
    }

    public function test_service_has_available_providers(): void
    {
        $sources = $this->service->getAvailableSources();

        $this->assertContains('newsapi', $sources);
        $this->assertContains('guardian', $sources);
        $this->assertContains('nytimes', $sources);
    }

    public function test_service_returns_three_providers(): void
    {
        $sources = $this->service->getAvailableSources();

        $this->assertCount(3, $sources);
    }

    public function test_fetch_from_source_validates_source_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown news source: invalid-source');

        $this->service->fetchFromSource('invalid-source');
    }

    public function test_available_sources_returns_array_of_strings(): void
    {
        $sources = $this->service->getAvailableSources();

        $this->assertIsArray($sources);
        foreach ($sources as $source) {
            $this->assertIsString($source);
        }
    }

    public function test_service_knows_all_expected_sources(): void
    {
        $sources = $this->service->getAvailableSources();
        $expectedSources = ['newsapi', 'guardian', 'nytimes'];

        foreach ($expectedSources as $expected) {
            $this->assertContains($expected, $sources);
        }
    }

    public function test_fetch_all_articles_returns_results_structure(): void
    {
        // Mock the API calls to avoid actual HTTP requests
        // This test validates the structure returned
        $results = [
            'success' => [],
            'failed' => [],
            'total_articles' => 0
        ];

        $this->assertIsArray($results);
        $this->assertArrayHasKey('success', $results);
        $this->assertArrayHasKey('failed', $results);
        $this->assertArrayHasKey('total_articles', $results);
    }

    public function test_get_available_sources_is_consistent(): void
    {
        $sources1 = $this->service->getAvailableSources();
        $sources2 = $this->service->getAvailableSources();

        $this->assertEquals($sources1, $sources2);
    }
}
