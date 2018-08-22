<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'namespace'  => 'Admin',
    'middleware' => ['web', 'aven'],
], function () {
    Route::delete('content-block/{id}', [
        '\Netcore\Aven\Content\Http\Controllers\Admin\PageController@contentBlockDelete'
    ]);

    Route::get('pages/create/{channel}', [
        '\Netcore\Aven\Content\Aven\Resources\PageResource@create'
    ]);

    Route::get('pages/{page}/edit', [
        '\Netcore\Aven\Content\Aven\Resources\PageResource@edit'
    ]);
});