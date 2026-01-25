<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Technology',
            'Business',
            'Science',
            'Health',
            'Sports',
            'Entertainment',
            'Politics',
            'World',
            'Opinion',
            'Culture',
        ];
        
        $name = fake()->unique()->randomElement($categories);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
