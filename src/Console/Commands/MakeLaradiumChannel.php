<?php

namespace Laradium\Laradium\Content\Console\Commands;

use Artisan;
use Illuminate\Console\Command;
use File;

class MakeLaradiumChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laradium:channel {name} {--t}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Laradium channel';

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

        $channelDirectory = app_path('Laradium/Channels');
        if (!file_exists($channelDirectory)) {
            File::makeDirectory($channelDirectory, 0755, true);
            $this->info('Creating channels directory');
        }

        $dummyChannel = File::get(__DIR__ . '/../../../stubs/laradium-channel.stub');
        $channel = str_replace('{{namespace}}', $namespace, $dummyChannel);
        $channel = str_replace('{{page}}', $name, $channel);
        $channel = str_replace('{{channelNamespace}}', config('laradium-content.default_channels_models_directory', 'App'), $channel);

        $channelFilePath = app_path('Laradium/Channels/' . $name . 'Channel.php');

        if (!file_exists($channelFilePath)) {
            File::put($channelFilePath, $channel);
        }

        Artisan::call('make:model', ['name' => 'Models/Channels/' . $name]);
        if ($translations) {
            Artisan::call('make:model', ['name' => 'Models/Channels/Translations/' . $name . 'Translation']);
        }

        $this->info('Channel successfully created!');

        cache()->forget('laradium::channel-list');

        return;
    }
}
