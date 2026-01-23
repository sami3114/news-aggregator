<?php

namespace App\Providers;

use App\Contracts\ArticleRepositoryInterface;
use App\Contracts\AuthorRepositoryInterface;
use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\UserPreferenceRepositoryInterface;
use App\Repositories\ArticleRepository;
use App\Repositories\AuthorRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\UserPreferenceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(AuthorRepositoryInterface::class,AuthorRepository::class);
        $this->app->bind(UserPreferenceRepositoryInterface::class, UserPreferenceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
