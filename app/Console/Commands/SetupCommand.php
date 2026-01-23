<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup
                            {--fresh : Run fresh migrations (drops all tables)}
                            {--seed : Run database seeders}
                            {--fetch : Fetch news articles after setup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application (migrate, seed, fetch news)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Application Setup...');
        $this->newLine();

        // Step 1: Generate app key if not set
        $this->task('Generating application key', function () {
            $this->callSilently('key:generate', ['--force' => true]);
            return true;
        });

        // Step 2: Run migrations
        $this->task('Running database migrations', function () {
            if ($this->option('fresh')) {
                $this->callSilently('migrate:fresh', ['--force' => true]);
            } else {
                $this->callSilently('migrate', ['--force' => true]);
            }
            return true;
        });

        // Step 3: Run seeders if requested
        if ($this->option('seed')) {
            $this->task('Seeding database', function () {
                $this->callSilently('db:seed', ['--force' => true]);
                return true;
            });
        }

        // Step 4: Fetch news if requested
        if ($this->option('fetch')) {
            $this->task('Fetching news articles', function () {
                $this->callSilently('news:fetch');
                return true;
            });
        }

        // Step 5: Clear and cache config
        $this->task('Optimizing application', function () {
            $this->callSilently('optimize:clear');
            return true;
        });

        $this->newLine();
        $this->info('Application setup completed successfully!');
        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Run a task with output
     */
    protected function task(string $title, callable $task): void
    {
        $this->output->write("  <comment>→</comment> {$title}... ");

        try {
            $result = $task();
            if ($result !== false) {
                $this->output->writeln('<info>✓</info>');
            } else {
                $this->output->writeln('<error>✗</error>');
            }
        } catch (\Exception $e) {
            $this->output->writeln('<error>✗</error>');
            $this->error("    Error: {$e->getMessage()}");
        }
    }
}
