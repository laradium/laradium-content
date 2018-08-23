<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'namespace'  => 'Admin',
    'middleware' => ['web', 'aven'],
], function () {
    Route::delete('content-block/{id}', [
        'uses' => '\Netcore\Aven\Content\Http\Controllers\Admin\PageController@contentBlockDelete'
    ]);

    Route::get('pages/create/{channel}', [
        'uses' => '\Netcore\Aven\Content\Aven\Resources\PageResource@create'
    ]);

    Route::get('pages/{page}/edit', [
        'uses' => '\Netcore\Aven\Content\Aven\Resources\PageResource@edit'
    ]);
});