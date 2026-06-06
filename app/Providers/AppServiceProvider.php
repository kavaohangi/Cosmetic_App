<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\TerrainReport;
use App\Observers\MessageObserver;
use App\Observers\TerrainReportObserver;
use App\Policies\MessagePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TerrainReport::observe(TerrainReportObserver::class);
        Message::observe(MessageObserver::class);

        Gate::define('chat-with', [MessagePolicy::class, 'canChatWith']);
    }
}
