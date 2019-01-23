<?php

return [

    'channel_path' => 'App\\Laradium\\Channels',

    'widget_path' => 'App\\Laradium\\Widgets',

    'default_widget_models_directory' => 'App\\Models\\Widgets',

    'default_channels_models_directory' => 'App\\Models\\Channels',

    'layouts' => [
        'layouts.main' => 'Main',
        'layouts.hiw'  => 'How It Works',
    ],

    'use_homepage_slug' => false,

    'resolver' => [
        // Defines the middlewares the resolver route will use
        'middlewares'    => ['web'],

        // Change this to a function name, that will return the uri for the resolver route
        'custom_uri'     => null,

        // Will add iso code before slug
        'prepend_locale' => false,

        // Defines the controller and method, the resolver route will use
        'uses'           => '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@resolve'
    ],

    'sitemap' => [
        'middlewares' => [],

        'only_app_locale' => false,

        'custom_pages' => [
            //[
            //    'uri'        => '/path/to/page',
            //    'updated_at' => '2019-01-22'
            //],

            //'/another/path/to/page'
        ]
    ]
];