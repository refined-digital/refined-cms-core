<?php

use RefinedDigital\CMS\Modules\Core\Actions\Favicon;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PublicRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\FrontEndPublicRouteAggregate;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$baseMiddleware = [
    'web',
];

$adminMiddleware = [
    'web',
    'auth',
    'userLevel',
    'admin'
];

if (help()->isMultiTenancy()) {
    $baseMiddleware = array_merge($baseMiddleware, [
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ]);
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
        Route::redirect('/', 'refined/dashboard');

        Route::get('dashboard', [
            'as' => 'dashboard',
            'uses' => 'RefinedDigital\CMS\Modules\Core\Http\Controllers\DashboardController@index',
        ]);

        // generic full-screen workspace endpoints; {module} resolves through
        // CustomModuleAggregate to models using HasContentBlocks
        Route::get('workspace/{module}/list', [
            'as' => 'workspace.list',
            'uses' => 'RefinedDigital\CMS\Modules\Core\Http\Controllers\WorkspaceController@list',
        ]);
        Route::get('workspace/{module}/{id}/data', [
            'as' => 'workspace.data',
            'uses' => 'RefinedDigital\CMS\Modules\Core\Http\Controllers\WorkspaceController@data',
        ]);
        Route::get('workspace/{module}/{id}/preview', [
            'as' => 'workspace.preview',
            'uses' => 'RefinedDigital\CMS\Modules\Core\Http\Controllers\WorkspaceController@preview',
        ]);
        Route::post('workspace/{module}/{id}/preview', [
            'as' => 'workspace.preview.render',
            'uses' => 'RefinedDigital\CMS\Modules\Core\Http\Controllers\WorkspaceController@previewRender',
        ]);

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
// include the front end public routes
Route::middleware($baseMiddleware)
    ->group(function(){
        $publicRouteAggregate = app(FrontEndPublicRouteAggregate::class);
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

Route::middleware(['web'])
    ->get('sitemap.xml',   ['uses' => 'RefinedDigital\CMS\Modules\Pages\Http\Controllers\PageController@xmlSitemap']);

Route::middleware($baseMiddleware)
    ->namespace('RefinedDigital\CMS\Modules\Pages\Http\Controllers')
    ->group(function() {
        // the catch all
        Route::fallback('PageController@render');
    })
;
