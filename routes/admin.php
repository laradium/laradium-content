<?php

Route::group([
    'prefix'     => 'admin',
    'as'         => 'admin.',
    'namespace'  => 'Admin',
    'middleware' => ['web', 'laradium'],
], function () {
    Route::delete('content-block/{id}', '\Laradium\Laradium\Content\Http\Controllers\Admin\PageController@contentBlockDelete');

    Route::get('pages/create/{channel}', '\Laradium\Laradium\Content\Base\Resources\PageResource@create')->name('pages.create');

    Route::get('pages/{page}/edit', '\Laradium\Laradium\Content\Base\Resources\PageResource@edit')->name('pages.edit');
});