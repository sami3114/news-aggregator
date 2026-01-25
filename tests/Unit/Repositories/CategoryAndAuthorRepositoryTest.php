<?php

namespace Tests\Unit\Repositories;

use App\Models\Author;
use App\Models\Category;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAndAuthorRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryRepository $categoryRepository;
    protected AuthorRepository $authorRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = new CategoryRepository(new Category());
        $this->authorRepository = new AuthorRepository(new Author());
    }

    // Category Repository Tests
    public function test_category_repository_can_get_all_categories(): void
    {
        Category::factory()->count(5)->create();

        $categories = $this->categoryRepository->getCategories();

        $this->assertCount(5, $categories);
    }

    public function test_category_repository_orders_by_name(): void
    {
        Category::factory()->create(['name' => 'Zebra']);
        Category::factory()->create(['name' => 'Alpha']);
        Category::factory()->create(['name' => 'Beta']);

        $categories = $this->categoryRepository->getCategories();

        $this->assertEquals('Alpha', $categories[0]['name']);
        $this->assertEquals('Beta', $categories[1]['name']);
        $this->assertEquals('Zebra', $categories[2]['name']);
    }

    public function test_category_repository_can_upsert_and_map(): void
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'slug' => 'business', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->categoryRepository->upsertAndMap($categories);

        $this->assertCount(2, $map);
        $this->assertArrayHasKey('technology', $map);
        $this->assertArrayHasKey('business', $map);
        $this->assertIsInt($map['technology']);
        $this->assertIsInt($map['business']);
    }

    public function test_category_repository_upsert_updates_existing(): void
    {
        $category = Category::factory()->create([
            'name' => 'Old Name',
            'slug' => 'technology'
        ]);

        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->categoryRepository->upsertAndMap($categories);

        $this->assertEquals(1, Category::count());
        $this->assertDatabaseHas('categories', [
            'slug' => 'technology',
            'name' => 'Technology',
        ]);
    }

    public function test_category_repository_map_includes_all_upserted(): void
    {
        Category::factory()->create(['slug' => 'technology']);

        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'slug' => 'business', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->categoryRepository->upsertAndMap($categories);

        $this->assertCount(2, $map);
        $this->assertArrayHasKey('technology', $map);
        $this->assertArrayHasKey('business', $map);
    }

    // Author Repository Tests
    public function test_author_repository_can_get_all_authors(): void
    {
        Author::factory()->count(5)->create();

        $authors = $this->authorRepository->getAuthors();

        $this->assertCount(5, $authors);
    }

    public function test_author_repository_orders_by_name(): void
    {
        Author::factory()->create(['name' => 'Zara Smith']);
        Author::factory()->create(['name' => 'Alice Johnson']);
        Author::factory()->create(['name' => 'Bob Wilson']);

        $authors = $this->authorRepository->getAuthors();

        $this->assertEquals('Alice Johnson', $authors[0]['name']);
        $this->assertEquals('Bob Wilson', $authors[1]['name']);
        $this->assertEquals('Zara Smith', $authors[2]['name']);
    }

    public function test_author_repository_can_upsert_and_map(): void
    {
        $authors = [
            ['name' => 'John Doe', 'slug' => 'john-doe', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jane Smith', 'slug' => 'jane-smith', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->authorRepository->upsertAndMap($authors);

        $this->assertCount(2, $map);
        $this->assertArrayHasKey('john-doe', $map);
        $this->assertArrayHasKey('jane-smith', $map);
        $this->assertIsInt($map['john-doe']);
        $this->assertIsInt($map['jane-smith']);
    }

    public function test_author_repository_upsert_updates_existing(): void
    {
        $author = Author::factory()->create([
            'name' => 'Old Name',
            'slug' => 'john-doe'
        ]);

        $authors = [
            ['name' => 'John Doe', 'slug' => 'john-doe', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->authorRepository->upsertAndMap($authors);

        $this->assertEquals(1, Author::count());
        $this->assertDatabaseHas('authors', [
            'slug' => 'john-doe',
            'name' => 'John Doe',
        ]);
    }

    public function test_author_repository_map_includes_all_upserted(): void
    {
        Author::factory()->create(['slug' => 'john-doe']);

        $authors = [
            ['name' => 'John Doe', 'slug' => 'john-doe', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jane Smith', 'slug' => 'jane-smith', 'created_at' => now(), 'updated_at' => now()],
        ];

        $map = $this->authorRepository->upsertAndMap($authors);

        $this->assertCount(2, $map);
        $this->assertArrayHasKey('john-doe', $map);
        $this->assertArrayHasKey('jane-smith', $map);
    }

    public function test_repository_handles_empty_arrays(): void
    {
        $categoryMap = $this->categoryRepository->upsertAndMap([]);
        $authorMap = $this->authorRepository->upsertAndMap([]);

        $this->assertEmpty($categoryMap);
        $this->assertEmpty($authorMap);
    }
}
