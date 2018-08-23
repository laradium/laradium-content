<?php

namespace Netcore\Aven\Content\Providers;

use Netcore\Aven\Content\Console\Commands\MakeAvenChannel;
use Illuminate\Support\ServiceProvider;
use Netcore\Aven\Content\Console\Commands\MakeAvenWidget;
use Netcore\Aven\Content\Registries\ChannelRegistry;
use Netcore\Aven\Content\Registries\WidgetRegistry;

class AvenContentServiceProvider extends ServiceProvider
{

    public function boot()
    {

        $configPath = __DIR__ . '/../../config/aven-content.php';
        $this->mergeConfigFrom($configPath, 'aven-content');

        $this->publishes([
            $configPath => config_path('aven-content.php'),
        ], 'aven-content');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'aven-content');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/admin.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAvenChannel::class,
                MakeAvenWidget::class,
            ]);
        }
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

            foreach (config('aven-content.channels', []) as  $channel) {
                $registry->register($channel);
            }

            return $registry;
        });

        $this->app->singleton(WidgetRegistry::class, function () {
            $registry = new WidgetRegistry();

            foreach (config('aven-content.widgets', []) as  $channel) {
                $registry->register($channel);
            }

            return $registry;
        });
    }
}
