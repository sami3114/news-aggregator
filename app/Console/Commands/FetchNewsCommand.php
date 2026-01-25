<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class FetchNewsCommand extends Command
{
    protected $signature = 'news:fetch {--source= : Fetch from specific source (newsapi, guardian, nytimes)}';
    protected $description = 'Fetch news articles from configured sources';

    public function handle(NewsAggregatorService $aggregator): int
    {
        $source = $this->option('source');

        try {
            if ($source) {
                return $this->fetchFromSingleSource($aggregator, $source);
            }

            return $this->fetchFromAllSources($aggregator);
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    protected function fetchFromSingleSource(NewsAggregatorService $aggregator, string $source): int
    {
        $this->info("Fetching from: {$source}");
        $count = $aggregator->fetchFromSource($source);
        $this->info("✓ Successfully fetched {$count} articles from {$source}");
        return Command::SUCCESS;
    }

    protected function fetchFromAllSources(NewsAggregatorService $aggregator): int
    {
        $this->info('Fetching from all sources...');
        $results = $aggregator->fetchAllArticles();

        $this->newLine();
        $this->table(['Source', 'Status', 'Articles'], $this->formatResults($results));
        $this->newLine();
        $this->info("✓ Total articles fetched: {$results['total_articles']}");

        return empty($results['failed']) ? Command::SUCCESS : Command::FAILURE;
    }

    protected function formatResults(array $results): array
    {
        $rows = [];

        foreach ($results['success'] as $source => $count) {
            $rows[] = [$source, '<fg=green>✓ Success</>', $count];
        }

        foreach ($results['failed'] as $source => $error) {
            $rows[] = [$source, '<fg=red>✗ Failed</>', $error];
        }

        return $rows;
    }
}
