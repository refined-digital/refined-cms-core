<?php
Route::namespace('CMS\Modules\SiteSettings\Http\Controllers')
    ->group(function() {
        Route::get('site', function () {
            return redirect('/refined/site/settings');
        })->name('site.index');
    });