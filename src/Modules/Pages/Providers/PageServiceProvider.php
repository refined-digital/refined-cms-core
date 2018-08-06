<?php

namespace RefinedDigital\CMS\Modules\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('pages', [
            __DIR__.'/../Resources/views',
            app_path().'/views'
        ]);

        view()->addNamespace('templates', [
            base_path().'/resources/views/templates'
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        app(RouteAggregate::class)
            ->addRouteFile('page', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 1,
            'name' => 'Pages',
            'icon' => 'fas fa-home',
            'route' => 'pages',
            'activeFor' => ['pages', 'templates'],
            'children' => [
                (object) [ 'name' => 'Pages', 'route' => 'pages', 'activeFor' => ['pages']],
                (object) [ 'name' => 'Templates', 'route' => 'templates', 'activeFor' => ['templates']],
                (object) [ 'name' => 'Settings', 'route' => ['settings.index', 'pages'], 'activeFor' => ['settings']],
            ]
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);

        $this->mergeConfigFrom(__DIR__.'/../Config/page.php', 'page');
    }
}
