<?php

Route::namespace('{{FullName}}\Http\Controllers')
    ->group(function() {
        Route::resource('{{name}}', '{{Name}}Controller');
    })
;