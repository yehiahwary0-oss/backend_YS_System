<?php

namespace App\Providers;

use App\Domains\Search\Contracts\SearchDriver;
use App\Domains\Search\Drivers\PostgresSearchDriver;
use App\Domains\System\Services\AuditService;
use App\Domains\System\Services\FeatureFlagService;
use App\Domains\System\Services\MediaUploadService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singletons — one instance per request lifecycle
        $this->app->singleton(AuditService::class);
        $this->app->singleton(FeatureFlagService::class);
        $this->app->singleton(MediaUploadService::class);

        // Search driver binding — swap PostgresSearchDriver for MeilisearchDriver
        // when ready, without changing any controller or service code.
        $this->app->singleton(SearchDriver::class, PostgresSearchDriver::class);
    }

    public function boot(): void
    {
        $this->configureFactoryResolver();
        $this->configureRateLimiting();
        $this->configureModelSettings();
    }

    /**
     * Models live under App\Domains\{Domain}\Models\{Model} instead of the
     * default App\Models\{Model}. Laravel's default factory name guesser
     * assumes the flat App\Models namespace, so we override it here to
     * always resolve to Database\Factories\{Model}Factory regardless of
     * how deeply nested the model's namespace is.
     */
    private function configureFactoryResolver(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $basename = class_basename($modelName);
            return 'Database\\Factories\\' . $basename . 'Factory';
        });
    }

    private function configureRateLimiting(): void
    {
        // Public API
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(
                (int) config('security.rate_limits.public_api', 120)
            )->by($request->ip());
        });

        // Auth attempts — brute force protection
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(
                (int) config('security.rate_limits.auth_attempts', 5)
            )->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.',
                    'code'    => 'RATE_LIMIT_EXCEEDED',
                ], 429);
            });
        });

        // Contact form — 3 per hour per IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(
                (int) config('security.rate_limits.contact', 3)
            )->by($request->ip());
        });

        // Search — 60 per minute per IP
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }

    private function configureModelSettings(): void
    {
        // Prevent mass assignment silent discard in non-production
        \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes(
            ! app()->isProduction()
        );

        // N+1 detection in non-production
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(
            ! app()->isProduction()
        );
    }
}
