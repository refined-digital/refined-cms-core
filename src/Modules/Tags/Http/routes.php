<?php

Route::namespace('CMS\Modules\Tags\Http\Controllers')
    ->prefix('tags')
    ->as('tags.')
    ->group(function() {
        Route::get('get-all-tags', [
            'as' => 'get-all-tags',
            'uses' => 'TagController@getAllTags',
        ]);
    })
;