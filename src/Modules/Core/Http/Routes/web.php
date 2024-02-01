<?php

use RefinedDigital\CMS\Modules\Core\Actions\Favicon;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PublicRouteAggregate;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$baseMiddleware = [
    'web',
    'cacheResponse',
];

$adminMiddleware = array_merge([
    'auth',
    'userLevel',
    'admin'
], $baseMiddleware);

if (help()->isMultiTenancy()) {
    $baseMiddleware = array_merge($baseMiddleware, [
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ]);
}


// the login routes
if (!help()->isMultiTenancy()) {
    Route::middleware($baseMiddleware)
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
}

$prefix = 'refined';
if (help()->isMultiTenancy()) {
    $prefix = '{tenant}/refined';
    $adminMiddleware[] = InitializeTenancyByPath::class;
}
Route::middleware($adminMiddleware)
    ->as('refined.')
    ->prefix($prefix)
    ->group(function(){
        Route::redirect('/', 'refined/pages');

        Route::namespace('RefinedDigital\\')
            ->group(function() {
                $routeAggregate = app(RouteAggregate::class);

                foreach ($routeAggregate->getRouteFiles() as $routeFile)
                {
                    include($routeFile);
                }
            })
        ;

        Route::namespace('App\RefinedCMS\\')
            ->group(function() {
                $routeAggregate = app(CustomModuleRouteAggregate::class);

                foreach ($routeAggregate->getRouteFiles() as $routeFile)
                {
                    include($routeFile);
                }
            })
        ;

    })
;


// include the public routes
Route::middleware($baseMiddleware)
    ->as('refined.')
    ->namespace('RefinedDigital\\')
    ->group(function(){
        $publicRouteAggregate = app(PublicRouteAggregate::class);
        foreach ($publicRouteAggregate->getRouteFiles() as $routeFile)
        {
            include($routeFile);
        }
    })
;

Route::middleware($baseMiddleware)
    ->group(function() {
        // Favicons
        Route::get('android-chrome-192x192.png', Favicon::class);
        Route::get('android-chrome-256x256.png', Favicon::class);
        Route::get('apple-touch-icon.png', Favicon::class);
        Route::get('browserconfig.xml', Favicon::class);
        Route::get('favicon-16x16.png', Favicon::class);
        Route::get('favicon-32x32.png', Favicon::class);
        Route::get('favicon.ico', Favicon::class);
        Route::get('mstile-150x150.png', Favicon::class);
        Route::get('safari-pinned-tab.svg', Favicon::class);
        Route::get('site.webmanifest', Favicon::class);
    })
;

Route::middleware($baseMiddleware)
    ->namespace('RefinedDigital\CMS\Modules\Pages\Http\Controllers')
    ->group(function() {

        // generates the xml sitemap
        Route::get('sitemap.xml',   ['uses' => 'PageController@xmlSitemap']);

        // the catch all
        Route::fallback('PageController@render');
    })
;