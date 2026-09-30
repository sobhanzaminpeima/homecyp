<?php

namespace App\Providers;

use App\Services\Llm\LlmManager;
use App\Services\Search\MySqlVectorSearch;
use App\Services\Search\VectorSearchInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LlmManager::class);
        $this->app->bind(VectorSearchInterface::class, MySqlVectorSearch::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Some cPanel hosts still run MySQL/MariaDB with the old InnoDB row
        // format (innodb_large_prefix off), which caps indexed key length at
        // ~767-1000 bytes. A default VARCHAR(255) column in utf8mb4 (4 bytes/
        // char) exceeds that once uniquely indexed — this is Laravel's
        // documented fix. See DEPLOYMENT.md for the case that surfaced this.
        Schema::defaultStringLength(191);
    }
}
