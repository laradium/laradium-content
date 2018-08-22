<?php

namespace Netcore\Aven\Content\Console\Commands;

use Illuminate\Console\Command;
use File;

class MakeAvenChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aven:channel  {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Aven channel';

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
        $namespace = str_replace('\\', '', app()->getNamespace());

        $channelDirectory = app_path('Aven/Channels');
        if (!file_exists($channelDirectory)) {
            File::makeDirectory($channelDirectory, 0755, true);
            $this->info('Creating channels directory');
        }
        $dummyChannel = File::get(__DIR__.'/../../../stubs/aven-channel.stub');
        $channel = str_replace('{{namespace}}', $namespace, $dummyChannel);
        $channel = str_replace('{{page}}', $name, $channel);
        $channel = str_replace('{{channelNamespace}}', config('aven-content.default_channels_models_directory', 'App'), $channel);

        $channelFilePath = app_path('Aven/Channels/' . $name . 'Channel.php');

        if (file_exists($channelFilePath)) {
            $this->error('Channel already exists!');

            return;
        } else {
            File::put($channelFilePath, $channel);
            $this->info('Channel successfully created!');
        }

        return;
    }
}
