<?php

Route::namespace('CMS\Modules\Settings\Http\Controllers')
    ->group(function() {

        Route::get('{type}/settings', [
            'as'    => 'settings.index',
            'uses'  => 'SettingController@index'
        ]);

        Route::post('settings/{type}', [
            'as'    => 'settings.update',
            'uses'  => 'SettingController@updateSettings'
        ]);
    })
;