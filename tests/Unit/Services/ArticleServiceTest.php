<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new ArticleService(
            new ArticleRepository(new Article()),
            new AuthorRepository(new Author()),
            new CategoryRepository(new Category())
        );
    }

    public function test_bulk_insert_creates_authors(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'author_name' => 'John Doe',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
                'categories' => ['Technology', 'Business'],
            ],
        ];

        $this->service->bulkInsert($articles);

        $this->assertDatabaseHas('authors', [
            'name' => 'John Doe',
            'slug' => 'john-doe',
        ]);
    }

    public function test_bulk_insert_creates_categories(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
                'categories' => ['Technology', 'Business'],
            ],
        ];

        $this->service->bulkInsert($articles);

        $this->assertDatabaseHas('categories', [
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Business',
            'slug' => 'business',
        ]);
    }

    public function test_bulk_insert_creates_articles(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'author_name' => 'John Doe',
                'title' => 'Test Article',
                'description' => 'Test Description',
                'content' => 'Test Content',
                'url' => 'https://example.com/1',
                'image_url' => 'https://example.com/1.jpg',
                'categories' => ['Technology'],
            ],
        ];

        $count = $this->service->bulkInsert($articles);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('articles', [
            'external_id' => 'ext-1',
            'source' => 'newsapi',
            'title' => 'Test Article',
        ]);
    }

    public function test_bulk_insert_associates_author_with_article(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'author_name' => 'John Doe',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
                'categories' => [],
            ],
        ];

        $this->service->bulkInsert($articles);

        $author = Author::where('slug', 'john-doe')->first();
        $article = Article::where('external_id', 'ext-1')->first();

        $this->assertNotNull($author);
        $this->assertNotNull($article);
        $this->assertEquals($author->id, $article->author_id);
    }

    public function test_bulk_insert_associates_categories_with_article(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
                'categories' => ['Technology', 'Business'],
            ],
        ];

        $this->service->bulkInsert($articles);

        $article = Article::where('external_id', 'ext-1')->first();
        $article->load('categories');

        $this->assertCount(2, $article->categories);
        $this->assertTrue($article->categories->contains('slug', 'technology'));
        $this->assertTrue($article->categories->contains('slug', 'business'));
    }

    public function test_bulk_insert_handles_duplicate_authors(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'author_name' => 'John Doe',
                'title' => 'Test Article 1',
                'url' => 'https://example.com/1',
                'categories' => [],
            ],
            [
                'external_id' => 'ext-2',
                'source' => 'newsapi',
                'author_name' => 'John Doe',
                'title' => 'Test Article 2',
                'url' => 'https://example.com/2',
                'categories' => [],
            ],
        ];

        $this->service->bulkInsert($articles);

        $authors = Author::where('slug', 'john-doe')->get();
        $this->assertCount(1, $authors);
    }

    public function test_bulk_insert_handles_duplicate_categories(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article 1',
                'url' => 'https://example.com/1',
                'categories' => ['Technology', 'Business'],
            ],
            [
                'external_id' => 'ext-2',
                'source' => 'newsapi',
                'title' => 'Test Article 2',
                'url' => 'https://example.com/2',
                'categories' => ['Technology', 'Science'],
            ],
        ];

        $this->service->bulkInsert($articles);

        $technology = Category::where('slug', 'technology')->get();
        $this->assertCount(1, $technology);
        
        $allCategories = Category::all();
        $this->assertCount(3, $allCategories);
    }

    public function test_bulk_insert_without_author_name(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
                'categories' => [],
            ],
        ];

        $this->service->bulkInsert($articles);

        $article = Article::where('external_id', 'ext-1')->first();
        $this->assertNull($article->author_id);
    }

    public function test_bulk_insert_without_categories(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article',
                'url' => 'https://example.com/1',
            ],
        ];

        $this->service->bulkInsert($articles);

        $article = Article::where('external_id', 'ext-1')->first();
        $article->load('categories');
        
        $this->assertCount(0, $article->categories);
    }

    public function test_bulk_insert_returns_correct_count(): void
    {
        $articles = [
            [
                'external_id' => 'ext-1',
                'source' => 'newsapi',
                'title' => 'Test Article 1',
                'url' => 'https://example.com/1',
                'categories' => [],
            ],
            [
                'external_id' => 'ext-2',
                'source' => 'guardian',
                'title' => 'Test Article 2',
                'url' => 'https://example.com/2',
                'categories' => [],
            ],
        ];

        $count = $this->service->bulkInsert($articles);

        $this->assertEquals(2, $count);
        $this->assertEquals(2, Article::count());
    }

    public function test_bulk_insert_handles_empty_array(): void
    {
        $count = $this->service->bulkInsert([]);

        $this->assertEquals(0, $count);
        $this->assertEquals(0, Article::count());
    }
}
