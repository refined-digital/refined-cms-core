<?php

Route::namespace('CMS\Modules\Media\Http\Controllers')
    ->group(function() {

        Route::prefix('media')
            ->as('media.')
            ->group(function(){
            Route::get('/', [
                'as'    => 'index',
                'uses'  => 'MediaController@index'
            ]);

            Route::get('/get-tree', [
                'as' => 'get-tree',
                'uses' => 'MediaController@getTree',
            ]);

            Route::post('/upload-file', [
                'as' => 'upload-file',
                'uses' => 'MediaController@uploadFile',
            ]);

            Route::post('/update-parent', [
                'as' => 'update-parent',
                'uses' => 'MediaController@updateParent',
            ]);

            Route::put('{id}', [
                'as' => 'update',
                'uses' => 'MediaController@update',
            ]);

            Route::get('{id}', [
                'as' => 'show',
                'uses' => 'MediaController@show',
            ]);

            Route::delete('{id}', [
                'as' => 'destroy',
                'uses' => 'MediaController@destroy',
            ]);
        });


        Route::prefix('media/categories')
            ->as('media.categories.')
            ->group(function(){

            Route::put('{id}', [
                'as' => 'update',
                'uses' => 'MediaController@categoryUpdate',
            ]);

            Route::post('/', [
                'as' => 'store',
                'uses' => 'MediaController@categoryStore',
            ]);

            Route::post('position', [
                'as' => 'position',
                'uses' => 'MediaController@categoryPosition',
            ]);

            Route::put('{id}/update-parent', [
                'as' => 'update-parent',
                'uses' => 'MediaController@categoryUpdateParent',
            ]);

            Route::delete('{id}', [
                'as' => 'destroy',
                'uses' => 'MediaController@categoryDestroy',
            ]);

        });


    })
;