<?php

Route::namespace('CMS\Modules\Tags\Http\Controllers')
    ->group(function() {
        Route::get('tags/get-all-tags', [
            'as' => 'tags.get-all-tags',
            'uses' => 'TagController@getAllTags',
        ]);

        Route::resource('tags', 'TagController');

    })
;