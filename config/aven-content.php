<?php

return [
    
    'default_widget_models_directory'   => 'App\\Models\\Widgets',
    'default_channels_models_directory' => 'App\\Models\\Channels',

    'channels' => [
        \Netcore\Aven\Content\Aven\Channels\MainChannel::class,
//        \App\Aven\Channels\BlogChannel::class,
    ],

    'widgets' => [
//        \App\Aven\Widgets\HiwWidget::class,
    ],
];