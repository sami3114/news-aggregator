<?php

namespace Tests\Unit\Repositories;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Repositories\ArticleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ArticleRepository(new Article());
    }

    public function test_get_all_returns_paginated_articles(): void
    {
        Article::factory()->count(15)->create();

        $result = $this->repository->getAll([], 10);

        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_all_filters_by_search_keyword(): void
    {
        Article::factory()->create([
            'title' => 'Laravel Testing',
            'description' => 'Testing guide'
        ]);
        
        Article::factory()->create([
            'title' => 'PHP Programming',
            'description' => 'PHP guide'
        ]);

        $result = $this->repository->getAll(['q' => 'Laravel'], 10);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('Laravel', $result->items()[0]->title);
    }

    public function test_get_all_filters_by_source(): void
    {
        Article::factory()->create(['source' => 'newsapi']);
        Article::factory()->create(['source' => 'guardian']);
        Article::factory()->create(['source' => 'newsapi']);

        $result = $this->repository->getAll(['source' => 'newsapi'], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_get_all_filters_by_category(): void
    {
        $category = Category::factory()->create(['slug' => 'technology']);
        $article = Article::factory()->create();
        $article->categories()->attach($category->id);

        Article::factory()->create();

        $result = $this->repository->getAll(['category' => 'technology'], 10);

        $this->assertCount(1, $result->items());
    }

    public function test_get_all_filters_by_author(): void
    {
        $author = Author::factory()->create();
        Article::factory()->count(2)->create(['author_id' => $author->id]);
        Article::factory()->create();

        $result = $this->repository->getAll(['author_id' => $author->id], 10);

        $this->assertCount(2, $result->items());
    }

    public function test_get_all_filters_by_date_range(): void
    {
        Article::factory()->create(['published_at' => '2024-01-05']);
        Article::factory()->create(['published_at' => '2024-01-15']);
        Article::factory()->create(['published_at' => '2024-01-25']);

        $result = $this->repository->getAll([
            'from_date' => '2024-01-10',
            'to_date' => '2024-01-20'
        ], 10);

        $this->assertCount(1, $result->items());
    }

    public function test_get_all_loads_relationships(): void
    {
        $author = Author::factory()->create();
        $category = Category::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);
        $article->categories()->attach($category->id);

        $result = $this->repository->getAll([], 10);

        $this->assertTrue($result->items()[0]->relationLoaded('author'));
        $this->assertTrue($result->items()[0]->relationLoaded('categories'));
    }

    public function test_bulk_upsert_inserts_new_articles(): void
    {
        $now = now();
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'source_name' => 'News API',
                'author_id' => null,
                'title' => 'Test Article 1',
                'description' => 'Description 1',
                'content' => 'Content 1',
                'url' => 'https://example.com/1',
                'image_url' => 'https://example.com/1.jpg',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $count = $this->repository->bulkUpsert($articles, []);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('articles', [
            'external_id' => 'ext-1',
            'source' => 'newsapi',
            'title' => 'Test Article 1',
        ]);
    }

    public function test_bulk_upsert_updates_existing_articles(): void
    {
        $article = Article::factory()->create([
            'external_id' => 'ext-1',
            'source' => 'newsapi',
            'title' => 'Old Title',
        ]);

        $now = now();
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'source_name' => 'News API',
                'author_id' => null,
                'title' => 'Updated Title',
                'description' => 'Description',
                'content' => 'Content',
                'url' => 'https://example.com/1',
                'image_url' => 'https://example.com/1.jpg',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->repository->bulkUpsert($articles, []);

        $this->assertDatabaseHas('articles', [
            'external_id' => 'ext-1',
            'source' => 'newsapi',
            'title' => 'Updated Title',
        ]);
        $this->assertEquals(1, Article::count());
    }

    public function test_bulk_upsert_attaches_categories(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->create(['external_id' => 'ext-1', 'source' => 'newsapi']);

        $categoryPivot = [
            [
                'article_id' => $article->id,
                'category_id' => $category->id,
            ],
        ];

        $this->repository->bulkUpsert([], $categoryPivot);

        $this->assertDatabaseHas('article_category', [
            'article_id' => $article->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_get_sources_returns_unique_sources(): void
    {
        Article::factory()->create(['source' => 'newsapi', 'source_name' => 'News API']);
        Article::factory()->create(['source' => 'guardian', 'source_name' => 'The Guardian']);
        Article::factory()->create(['source' => 'newsapi', 'source_name' => 'News API']);

        $sources = $this->repository->getSources();

        $this->assertCount(2, $sources);
        $this->assertEquals('newsapi', $sources[0]['id']);
        $this->assertEquals('News API', $sources[0]['name']);
    }

    public function test_get_all_orders_by_published_date_descending(): void
    {
        Article::factory()->create(['published_at' => '2024-01-10', 'title' => 'Old']);
        Article::factory()->create(['published_at' => '2024-01-20', 'title' => 'New']);
        Article::factory()->create(['published_at' => '2024-01-15', 'title' => 'Middle']);

        $result = $this->repository->getAll([], 10);

        $this->assertEquals('New', $result->items()[0]->title);
        $this->assertEquals('Middle', $result->items()[1]->title);
        $this->assertEquals('Old', $result->items()[2]->title);
    }
}
