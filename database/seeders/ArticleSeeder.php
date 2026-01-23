<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = Author::all();
        $categories = Category::all();

        if ($authors->isEmpty() || $categories->isEmpty()) {
            $this->command->warn('Please run AuthorSeeder and CategorySeeder first.');
            return;
        }

        $articles = [
            [
                'external_id' => 'seed-article-001',
                'title' => 'The Future of Artificial Intelligence in Healthcare',
                'description' => 'How AI is revolutionizing medical diagnosis and treatment planning.',
                'content' => 'Artificial intelligence is transforming healthcare at an unprecedented pace. From early disease detection to personalized treatment plans, AI algorithms are helping doctors make better decisions faster.',
                'source' => 'newsapi',
                'source_name' => 'Tech News Daily',
                'categories' => ['Technology', 'Health'],
            ],
            [
                'external_id' => 'seed-article-002',
                'title' => 'Global Markets Rally Amid Economic Optimism',
                'description' => 'Stock markets worldwide show strong gains as investors respond to positive economic indicators.',
                'content' => 'Global stock markets experienced significant gains today as investors reacted positively to better-than-expected economic data from major economies.',
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'categories' => ['Business', 'World'],
            ],
            [
                'external_id' => 'seed-article-003',
                'title' => 'Breakthrough in Renewable Energy Storage',
                'description' => 'Scientists develop new battery technology that could revolutionize solar power storage.',
                'content' => 'Researchers have announced a major breakthrough in energy storage technology that could make renewable energy more practical and affordable.',
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'categories' => ['Science', 'Technology'],
            ],
            [
                'external_id' => 'seed-article-004',
                'title' => 'Championship Finals Draw Record Viewership',
                'description' => 'The highly anticipated finals attracted millions of viewers worldwide.',
                'content' => 'The championship finals broke viewing records with an estimated 50 million viewers tuning in globally to watch the thrilling conclusion.',
                'source' => 'newsapi',
                'source_name' => 'Sports Weekly',
                'categories' => ['Sports', 'Entertainment'],
            ],
            [
                'external_id' => 'seed-article-005',
                'title' => 'New Study Reveals Benefits of Mediterranean Diet',
                'description' => 'Research shows significant health improvements for those following Mediterranean eating patterns.',
                'content' => 'A comprehensive study published today confirms the numerous health benefits associated with the Mediterranean diet, including reduced risk of heart disease.',
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'categories' => ['Health', 'Lifestyle'],
            ],
            [
                'external_id' => 'seed-article-006',
                'title' => 'Tech Giants Announce Collaboration on Privacy Standards',
                'description' => 'Major technology companies agree to implement unified data protection measures.',
                'content' => 'In an unprecedented move, leading technology companies have announced a joint initiative to establish industry-wide privacy standards.',
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'categories' => ['Technology', 'Business'],
            ],
            [
                'external_id' => 'seed-article-007',
                'title' => 'Climate Summit Reaches Historic Agreement',
                'description' => 'World leaders commit to ambitious carbon reduction targets at international summit.',
                'content' => 'The global climate summit concluded with a historic agreement as nations pledged to significantly reduce carbon emissions over the next decade.',
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'categories' => ['Politics', 'World'],
            ],
            [
                'external_id' => 'seed-article-008',
                'title' => 'Streaming Service Launches Revolutionary Platform',
                'description' => 'New streaming technology promises better quality and lower costs for consumers.',
                'content' => 'A new streaming service has launched with innovative technology that delivers higher quality video at lower bandwidth costs.',
                'source' => 'newsapi',
                'source_name' => 'Entertainment Today',
                'categories' => ['Entertainment', 'Technology'],
            ],
            [
                'external_id' => 'seed-article-009',
                'title' => 'Space Tourism Takes Off with First Commercial Flight',
                'description' => 'Private space company successfully launches first paying passengers to orbit.',
                'content' => 'History was made today as the first commercial space tourism flight successfully carried paying passengers beyond Earth\'s atmosphere.',
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'categories' => ['Science', 'Travel'],
            ],
            [
                'external_id' => 'seed-article-010',
                'title' => 'Electric Vehicle Sales Surge Globally',
                'description' => 'EV adoption accelerates as more affordable models hit the market.',
                'content' => 'Electric vehicle sales have reached record highs as manufacturers introduce more affordable options and charging infrastructure expands.',
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'categories' => ['Business', 'Technology'],
            ],
            [
                'external_id' => 'seed-article-011',
                'title' => 'Mental Health Apps See Unprecedented Growth',
                'description' => 'Digital wellness tools gain popularity as people prioritize mental health.',
                'content' => 'Mental health applications have experienced significant growth as more people turn to digital tools for stress management and emotional wellbeing.',
                'source' => 'newsapi',
                'source_name' => 'Health Monitor',
                'categories' => ['Health', 'Technology'],
            ],
            [
                'external_id' => 'seed-article-012',
                'title' => 'Major City Unveils Smart Transportation Network',
                'description' => 'Integrated public transit system uses AI to optimize routes in real-time.',
                'content' => 'The city has launched an ambitious smart transportation initiative that uses artificial intelligence to coordinate buses, trains, and ride-sharing services.',
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'categories' => ['Technology', 'Lifestyle'],
            ],
            [
                'external_id' => 'seed-article-013',
                'title' => 'Esports Tournament Offers Record Prize Pool',
                'description' => 'Gaming competition attracts top players with multi-million dollar prizes.',
                'content' => 'The world\'s largest esports tournament has announced a record-breaking prize pool, attracting elite gamers from around the globe.',
                'source' => 'newsapi',
                'source_name' => 'Gaming World',
                'categories' => ['Sports', 'Entertainment'],
            ],
            [
                'external_id' => 'seed-article-014',
                'title' => 'Sustainable Fashion Movement Gains Momentum',
                'description' => 'Major brands commit to eco-friendly practices and materials.',
                'content' => 'The fashion industry is undergoing a sustainability transformation as major brands announce commitments to reduce environmental impact.',
                'source' => 'guardian',
                'source_name' => 'The Guardian',
                'categories' => ['Lifestyle', 'Business'],
            ],
            [
                'external_id' => 'seed-article-015',
                'title' => 'Researchers Discover New Species in Deep Ocean',
                'description' => 'Marine expedition uncovers previously unknown creatures in unexplored waters.',
                'content' => 'A deep-sea research expedition has discovered several new species of marine life in one of the ocean\'s least explored regions.',
                'source' => 'nytimes',
                'source_name' => 'The New York Times',
                'categories' => ['Science', 'World'],
            ],
        ];

        foreach ($articles as $index => $articleData) {
            $author = $authors->random();

            // Create or find article
            $article = Article::firstOrCreate(
                [
                    'external_id' => $articleData['external_id'],
                    'source' => $articleData['source'],
                ],
                [
                    'source_name' => $articleData['source_name'],
                    'author_id' => $author->id,
                    'title' => $articleData['title'],
                    'description' => $articleData['description'],
                    'content' => $articleData['content'],
                    'url' => 'https://example.com/article/' . Str::slug($articleData['title']),
                    'image_url' => 'https://picsum.photos/seed/' . ($index + 1) . '/800/600',
                    'published_at' => now()->subHours(rand(1, 72)),
                ]
            );

            // Sync categories
            $categoryIds = Category::whereIn('name', $articleData['categories'])->pluck('id');
            $article->categories()->syncWithoutDetaching($categoryIds);
        }
    }
}
