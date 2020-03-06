<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'namespace'  => 'Admin',
    'middleware' => ['web', 'laradium'],
], function () {
    Route::put('pages/{page}/duplicate', '\Laradium\Laradium\Content\Base\Resources\PageResource@duplicate')->name('pages.duplicate');
    Route::delete('content-block/{id}', '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@contentBlockDelete');
    Route::get('pages/create/{channel}', '\Laradium\Laradium\Content\Base\Resources\PageResource@create')->name('pages.create');
});

Route::middleware(config('laradium-content.sitemap.middlewares', []))->group(function () {
    Route::get('sitemap.xml', config('laradium-content.sitemap.uses', '\Laradium\Laradium\Content\Http\Controllers\SitemapController@index'));
});