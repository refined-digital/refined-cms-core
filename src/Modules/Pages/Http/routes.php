<?php

Route::namespace('CMS\Modules\Pages\Http\Controllers')
    ->group(function() {
        Route::get('pages/get-tree', [
            'as' => 'pages.get-tree',
            'uses' => 'PageController@getTree',
        ]);

        Route::get('pages/get-tree-basic', [
            'as' => 'pages.get-tree-basic',
            'uses' => 'PageController@getTreeBasic',
        ]);

        Route::put('pages/{id}/update-parent', [
            'as' => 'pages.update-parent',
            'uses' => 'PageController@updateParent',
        ]);

        Route::post('pages/{id}/duplicate', [
            'as' => 'pages.duplicate',
            'uses' => 'PageController@duplicate',
        ]);

        Route::get('pages/{id}/preview', [
            'as' => 'pages.preview',
            'uses' => 'PagePreviewController@show',
        ]);

        Route::post('pages/{id}/preview', [
            'as' => 'pages.preview.render',
            'uses' => 'PagePreviewController@render',
        ]);

        // the page tree posts to pages.duplicate above, a resource duplicate route would reuse its name
        Route::resource('pages', 'PageController')->except(['duplicate']);
    })
;

Route::namespace('CMS\Modules\Pages\Http\Controllers')
    ->group(function() {
        Route::resource('templates', 'TemplateController');
    })
;