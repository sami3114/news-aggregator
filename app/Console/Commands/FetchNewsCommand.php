<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch
                            {--source= : Fetch from specific source (newsapi, guardian, nytimes)}
                            {--all : Fetch from all sources}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from configured sources';

    /**
     * Execute the console command.
     */
    public function handle(NewsAggregatorService $aggregator): int
    {
        $source = $this->option('source');

        $this->info('Starting news fetch...');

        try {
            if ($source) {
                $this->info("Fetching from: {$source}");
                $count = $aggregator->fetchFromSource($source);
                $this->info("Successfully fetched {$count} articles from {$source}");
            } else {
                $this->info('Fetching from all sources...');
                $results = $aggregator->fetchAllArticles();

                $this->newLine();
                $this->info('Fetch Results:');
                $this->table(
                    ['Source', 'Status', 'Articles'],
                    $this->formatResults($results)
                );

                $this->newLine();
                $this->info("Total articles fetched: {$results['total_articles']}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Format results for table display
     */
    protected function formatResults(array $results): array
    {
        $rows = [];

        foreach ($results['success'] as $source => $count) {
            $rows[] = [$source, '<fg=green>Success</>', $count];
        }

        foreach ($results['failed'] as $source => $error) {
            $rows[] = [$source, '<fg=red>Failed</>', $error];
        }

        return $rows;
    }
}
