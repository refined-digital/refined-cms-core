<?php

Route::namespace('CMS\Modules\Users\Http\Controllers')
    ->group(function() {
        Route::resource('users', 'UserController');
        Route::resource('user-groups', 'UserGroupController');
    })
;
