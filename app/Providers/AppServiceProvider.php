<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\TerrainReport;
use App\Observers\MessageObserver;
use App\Observers\TerrainReportObserver;
use App\Policies\MessagePolicy;
use App\Services\ConversionService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConversionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TerrainReport::observe(TerrainReportObserver::class);
        Message::observe(MessageObserver::class);

        Gate::define('chat-with', [MessagePolicy::class, 'canChatWith']);

        // @money($usd) -> "$12.00 (24 000 FC)" using the company conversion rate.
        Blade::directive('money', function (string $expression): string {
            return "<?php echo app(\\App\\Services\\ConversionService::class)->format({$expression}); ?>";
        });
    }
}
