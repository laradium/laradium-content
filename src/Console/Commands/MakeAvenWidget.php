<?php

namespace Netcore\Aven\Content\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use File;

class MakeAvenWidget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aven:widget {name} {--t}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Aven widget';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $name = $this->argument('name');
        $translations = $this->option('t');
        $namespace = str_replace('\\', '', app()->getNamespace());

        $widgetDirectory = app_path('Aven/Widgets');
        if (!file_exists($widgetDirectory)) {
            File::makeDirectory($widgetDirectory, 0755, true);
            $this->info('Creating widgets directory');
        }

        $dummyWidget = File::get(__DIR__ . '/../../../stubs/aven-widget.stub');
        $widget = str_replace('{{namespace}}', $namespace, $dummyWidget);
        $widget = str_replace('{{widget}}', $name, $widget);
        $widget = str_replace('{{widgetNamespace}}', config('aven-content.default_widget_models_directory', 'App'), $widget);

        $widgetFilePath = app_path('Aven/Widgets/' . $name . 'Widget.php');

        if (!file_exists($widgetFilePath)) {
            File::put($widgetFilePath, $widget);
        }

        Artisan::call('make:model', ['name' => 'Models/Widgets/' . $name]);
        if ($translations) {
            Artisan::call('make:model', ['name' => 'Models/Widgets/Translations/' . $name . 'Translation']);
        }

        $this->info('Widget successfully created!');

        return;
    }
}
