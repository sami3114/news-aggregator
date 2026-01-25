<?php

namespace Tests\Unit\Models;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAndAuthorModelTest extends TestCase
{
    use RefreshDatabase;

    // Category Tests
    public function test_category_has_fillable_attributes(): void
    {
        $category = new Category();
        
        $fillable = ['name', 'slug'];

        $this->assertEquals($fillable, $category->getFillable());
    }

    public function test_category_belongs_to_many_articles(): void
    {
        $category = Category::factory()->create();
        $articles = Article::factory()->count(3)->create();
        
        $category->articles()->attach($articles->pluck('id'));

        $this->assertCount(3, $category->articles);
        $this->assertInstanceOf(Article::class, $category->articles->first());
    }

    public function test_category_slug_is_unique(): void
    {
        Category::factory()->create(['slug' => 'technology']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Category::factory()->create(['slug' => 'technology']);
    }

    public function test_category_can_be_created_with_name_and_slug(): void
    {
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
    }

    // Author Tests
    public function test_author_has_fillable_attributes(): void
    {
        $author = new Author();
        
        $fillable = ['name', 'slug'];

        $this->assertEquals($fillable, $author->getFillable());
    }

    public function test_author_has_many_articles(): void
    {
        $author = Author::factory()->create();
        Article::factory()->count(3)->create(['author_id' => $author->id]);

        $this->assertCount(3, $author->articles);
        $this->assertInstanceOf(Article::class, $author->articles->first());
    }

    public function test_author_slug_is_unique(): void
    {
        Author::factory()->create(['slug' => 'john-doe']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Author::factory()->create(['slug' => 'john-doe']);
    }

    public function test_author_can_be_created_with_name_and_slug(): void
    {
        $author = Author::create([
            'name' => 'John Doe',
            'slug' => 'john-doe',
        ]);

        $this->assertDatabaseHas('authors', [
            'name' => 'John Doe',
            'slug' => 'john-doe',
        ]);
    }

    public function test_deleting_category_detaches_articles(): void
    {
        $category = Category::factory()->create();
        $article = Article::factory()->create();
        $article->categories()->attach($category->id);

        $this->assertDatabaseHas('article_category', [
            'category_id' => $category->id,
            'article_id' => $article->id,
        ]);

        $category->delete();

        $this->assertDatabaseMissing('article_category', [
            'category_id' => $category->id,
        ]);
    }

    public function test_deleting_author_does_not_delete_articles(): void
    {
        $author = Author::factory()->create();
        $article = Article::factory()->create(['author_id' => $author->id]);

        $author->delete();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
        ]);
    }
}
