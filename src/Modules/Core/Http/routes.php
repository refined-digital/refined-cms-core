<?php

use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

// the login routes
Route::middleware(['web'])
    ->namespace('RefinedDigital\CMS\Modules\Users\Http\Controllers')
    ->group(function(){
        Route::redirect('/home', 'refined/pages');

        // Authentication Routes...
        Route::get('login', 'LoginController@showLoginForm')->name('login');
        Route::post('login', 'LoginController@login');
        Route::get('logout', 'LoginController@logout')->name('logout');
        Route::post('logout', 'LoginController@logout');

        // Password Reset Routes...
        Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
        Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
        Route::post('password/reset', 'ResetPasswordController@reset');
    })
;


Route::middleware(['web', 'auth'])
    ->as('refined.')
    ->namespace('RefinedDigital\\')
    ->prefix('refined')
    ->group(function(){
        Route::redirect('/', 'refined/pages');

        $routeAggregate = app(RouteAggregate::class);

        foreach ($routeAggregate->getRouteFiles() as $routeFile)
        {
            include($routeFile);
        }
    })
;

Route::namespace('RefinedDigital\\CMS\\Modules\\Pages\\Http\\Controllers')
    ->group(function() {
        Route::get('sitemap.xml',   ['uses' => 'PageController@xmlSitemap']);
        Route::get('{uri}',         ['uses'=>'PageController@render'])->where('uri', '(.*)');
    })
;