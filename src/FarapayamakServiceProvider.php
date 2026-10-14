<?php

namespace Farapayamak\Laravel;

use Farapayamak\Laravel\Channels\FarapayamakChannel;
use Farapayamak\Laravel\Console\CreditCommand;
use Farapayamak\Laravel\Console\NumbersCommand;
use Farapayamak\Laravel\Console\PatternsCommand;
use Farapayamak\Laravel\Console\PatternSendCommand;
use Farapayamak\Laravel\Console\SendCommand;
use Farapayamak\Laravel\Http\Controllers\FarapayamakWebhookController;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FarapayamakServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/farapayamak.php', 'farapayamak');

        // Bind FarapayamakManager singleton
        $this->app->singleton('farapayamak', function ($app) {
            return new FarapayamakManager($app);
        });

        $this->app->alias('farapayamak', FarapayamakManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load database migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publishing configuration & migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/farapayamak.php' => config_path('farapayamak.php'),
            ], 'farapayamak-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'farapayamak-migrations');

            // Register Artisan commands
            $this->commands([
                CreditCommand::class,
                NumbersCommand::class,
                PatternsCommand::class,
                SendCommand::class,
                PatternSendCommand::class,
            ]);
        }

        // Register Laravel Notification Channel
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('farapayamak', function ($app) {
                return $app->make(FarapayamakChannel::class);
            });
        });

        // Register Webhook routes if enabled
        $this->registerWebhookRoutes();
    }

    /**
     * Register incoming webhook routes.
     */
    protected function registerWebhookRoutes(): void
    {
        if (config('farapayamak.webhook.enabled', false)) {
            $routeUri = config('farapayamak.webhook.route', 'farapayamak/webhook');

            Route::post($routeUri, [FarapayamakWebhookController::class, 'handle'])
                ->name('farapayamak.webhook');
        }
    }
}
