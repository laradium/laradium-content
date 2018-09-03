<?php

return [
    
    'default_widget_models_directory'   => 'App\\Models\\Widgets',
    'default_channels_models_directory' => 'App\\Models\\Channels',

    'channels' => [
        \Laradium\Laradium\Content\Laradium\Channels\MainChannel::class,
//        \App\Laradium\Channels\BlogChannel::class,
    ],

    'widgets' => [
//        \App\Laradium\Widgets\HiwWidget::class,
    ],
];