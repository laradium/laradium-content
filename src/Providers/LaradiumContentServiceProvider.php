<?php

namespace Laradium\Laradium\Content\Providers;

use Illuminate\Support\ServiceProvider;
use Laradium\Laradium\Content\Console\Commands\MakeLaradiumChannel;
use Laradium\Laradium\Content\Console\Commands\MakeLaradiumWidget;
use Laradium\Laradium\Content\Registries\ChannelRegistry;
use Laradium\Laradium\Content\Registries\WidgetRegistry;

class LaradiumContentServiceProvider extends ServiceProvider
{

    /**
     * Boot method
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/laradium-content.php';
        $assetPath = __DIR__ . '/../../public/laradium';
        $this->mergeConfigFrom($configPath, 'laradium-content');

        $this->publishes([
            $configPath => config_path('laradium-content.php'),
            $assetPath  => public_path('laradium')
        ], 'laradium-content');

        $this->publishes([
            $assetPath  => public_path('laradium')
        ], 'laradium-content-assets');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'laradium-content');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');

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

            foreach ($this->getChannelList() as $channel) {
                $registry->register($channel);
            }

            return $registry;
        });

        $this->app->singleton(WidgetRegistry::class, function () {
            $registry = new WidgetRegistry();

            foreach ($this->getWidgetList() as $channel) {
                $registry->register($channel);
            }

            return $registry;
        });
    }

    /**
     * Get widgets
     *
     * @return mixed
     */
    private function getWidgetList()
    {
        $widgetList = [];
        $widgets = config('laradium-content.widget_path', 'App\\Laradium\\Widgets');
        $namespace = app()->getNamespace();
        $widgetPath = str_replace($namespace, '', $widgets);
        $widgetPath = str_replace('\\', '/', $widgetPath);
        $widgetPath = app_path($widgetPath);
        if (file_exists($widgetPath)) {
            foreach (\File::allFiles($widgetPath) as $path) {
                $widget = $path->getPathname();
                $baseName = basename($widget, '.php');
                $widget = $widgets . '\\' . $baseName;
                $widgetList[] = $widget;
            }
        }

        return $widgetList;
    }

    /**
     * Get channels
     *
     * @return mixed
     */
    private function getChannelList()
    {
        $channelPath = base_path('vendor/laradium/laradium-content/src/Base/Channels');

        $channelList = [];
        if (file_exists($channelPath)) {
            foreach (\File::allFiles($channelPath) as $path) {
                $channel = $path->getPathname();
                $baseName = basename($channel, '.php');
                $channel = 'Laradium\\Laradium\\Content\\Base\\Channels\\' . $baseName;
                $channelList[] = $channel;
            }
        }

        $channels = config('laradium-content.channel_path', 'App\\Laradium\\Channels');
        $namespace = app()->getNamespace();
        $channelPath = str_replace($namespace, '', $channels);
        $channelPath = str_replace('\\', '/', $channelPath);
        $channelPath = app_path($channelPath);
        if (file_exists($channelPath)) {
            foreach (\File::allFiles($channelPath) as $path) {
                $channel = $path->getPathname();
                $baseName = basename($channel, '.php');
                $channel = $channels . '\\' . $baseName;
                $channelList[] = $channel;
            }
        }

        return $channelList;
    }
}
