<?php

namespace Laradium\Laradium\Content\Providers;

use Laradium\Laradium\Content\Console\Commands\MakeLaradiumChannel;
use Illuminate\Support\ServiceProvider;
use Laradium\Laradium\Content\Console\Commands\MakeLaradiumWidget;
use Laradium\Laradium\Content\Registries\ChannelRegistry;
use Laradium\Laradium\Content\Registries\WidgetRegistry;

class LaradiumContentServiceProvider extends ServiceProvider
{

    public function boot()
    {

        $configPath = __DIR__ . '/../../config/laradium-content.php';
        $this->mergeConfigFrom($configPath, 'laradium-content');

        $this->publishes([
            $configPath => config_path('laradium-content.php'),
        ], 'laradium-content');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'laradium-content');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeLaradiumChannel::class,
                MakeLaradiumWidget::class,
            ]);
        }

        // Global helpers
        require_once __DIR__ . '/../Helpers/Global.php';
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ChannelRegistry::class, function () {
            $registry = new ChannelRegistry();

            foreach (config('laradium-content.channels', []) as  $channel) {
                $registry->register($channel);
            }

            return $registry;
        });

        $this->app->singleton(WidgetRegistry::class, function () {
            $registry = new WidgetRegistry();

            foreach (config('laradium-content.widgets', []) as  $channel) {
                $registry->register($channel);
            }

            return $registry;
        });
    }
}
