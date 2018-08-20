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

        Route::resource('pages', 'PageController');
    })
;

Route::namespace('CMS\Modules\Pages\Http\Controllers')
    ->group(function() {
        Route::resource('templates', 'TemplateController');
    })
;