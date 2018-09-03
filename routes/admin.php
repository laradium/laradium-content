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
        'uses' => '\Laradium\Laradium\Content\Laradium\Resources\PageResource@create'
    ]);

    Route::get('pages/{page}/edit', [
        'uses' => '\Laradium\Laradium\Content\Laradium\Resources\PageResource@edit'
    ]);
});

//Route::get('/{slug?}', [
//    'uses' => '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@resolve'
//])->middleware('web');