<?php

return [

    'channel_path' => 'App\\Laradium\\Channels',

    'widget_path' => 'App\\Laradium\\Widgets',
    
    'default_widget_models_directory'   => 'App\\Models\\Widgets',

    'default_channels_models_directory' => 'App\\Models\\Channels',

    'layouts' => [
        'layouts.main' => 'Main',
        'layouts.hiw'  => 'How It Works',
    ],

    'use_homepage_slug' => false,
];