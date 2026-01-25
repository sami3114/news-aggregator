<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_has_fillable_attributes(): void
    {
        $article = new Article();

        $fillable = [
            'external_id',
            'source',
            'source_name',
            'author_id',
            'title',
            'description',
            'content',
            'url',
            'image_url',
            'category',
            'published_at',
        ];

        $this->assertEquals($fillable, $article->getFillable());
    }

    public function test_article_belongs_to_author(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);

        $this->assertInstanceOf(Author::class, $article->author);
        $this->assertEquals($author->id, $article->author->id);
    }

    public function test_article_belongs_to_many_categories(): void
    {
        $article = Article::factory()->create();
        $categories = Category::factory()->count(3)->create();

        $article->categories()->attach($categories->pluck('id'));

        $this->assertCount(3, $article->categories);
        $this->assertInstanceOf(Category::class, $article->categories->first());
    }

    public function test_article_can_search_by_keyword(): void
    {
        Article::factory()->create([
            'title' => 'Laravel Testing Tutorial',
            'description' => 'Learn how to test Laravel applications'
        ]);

        Article::factory()->create([
            'title' => 'PHP Best Practices',
            'description' => 'Modern PHP development'
        ]);

        $results = Article::search('Laravel')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Testing Tutorial', $results->first()->title);
    }

    public function test_article_can_filter_by_source(): void
    {
        Article::factory()->create(['source' => 'newsapi']);
        Article::factory()->create(['source' => 'guardian']);
        Article::factory()->create(['source' => 'newsapi']);

        $results = Article::bySource('newsapi')->get();

        $this->assertCount(2, $results);
        $this->assertEquals('newsapi', $results->first()->source);
    }

    public function test_article_can_filter_by_category_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'technology']);
        $article = Article::factory()->create();
        $article->categories()->attach($category->id);

        $otherArticle = Article::factory()->create();

        $results = Article::byCategorySlug('technology')->get();

        $this->assertCount(1, $results);
        $this->assertEquals($article->id, $results->first()->id);
    }

    public function test_article_can_filter_by_author(): void
    {
        $author = Author::factory()->create();
        Article::factory()->count(2)->create(['author_id' => $author->id]);
        Article::factory()->create();

        $results = Article::byAuthor($author->id)->get();

        $this->assertCount(2, $results);
        $this->assertEquals($author->id, $results->first()->author_id);
    }

    public function test_article_can_filter_between_dates(): void
    {
        Article::factory()->create(['published_at' => '2024-01-01 10:00:00']);
        Article::factory()->create(['published_at' => '2024-01-15 10:00:00']);
        Article::factory()->create(['published_at' => '2024-02-01 10:00:00']);

        $results = Article::betweenDates('2024-01-10', '2024-01-20')->get();

        $this->assertCount(1, $results);
    }

    public function test_article_casts_dates_correctly(): void
    {
        $article = Article::factory()->create([
            'published_at' => '2024-01-15 10:00:00'
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $article->published_at);
    }
}
