<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'namespace'  => 'Admin',
    'middleware' => ['web', 'laradium'],
], function () {
    Route::delete('content-block/{id}', [
        'uses' => '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@contentBlockDelete'
    ]);

    Route::get('pages/create/{channel}', [
        'uses' => '\Laradium\Laradium\Content\Base\Resources\PageResource@create',
        'as'   => 'pages.create'
    ]);

    Route::get('pages/{page}/edit', [
        'uses' => '\Laradium\Laradium\Content\Base\Resources\PageResource@edit',
        'as'   => 'pages.edit'
    ]);
});