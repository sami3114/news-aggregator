<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->uuid(),
            'source' => fake()->randomElement(['newsapi', 'guardian', 'nytimes']),
            'source_name' => fake()->randomElement(['News API', 'The Guardian', 'New York Times']),
            'author_id' => null,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'url' => fake()->url(),
            'image_url' => fake()->imageUrl(),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the article has an author.
     */
    public function withAuthor(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => Author::factory(),
        ]);
    }

    /**
     * Indicate that the article is from a specific source.
     */
    public function fromSource(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => $source,
        ]);
    }
}
