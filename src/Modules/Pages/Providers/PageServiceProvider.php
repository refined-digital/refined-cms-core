<?php

namespace RefinedDigital\CMS\Modules\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;

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
            base_path().'/resources/views',
            __DIR__.'/../Resources/views',
        ]);

        view()->addNamespace('templates', [
            base_path().'/resources/views/templates'
        ]);

        $this->publishes([
            __DIR__.'/../Config/pages.php' => config_path('pages.php'),
        ], 'pages');
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

        $this->app->singleton(PageAggregate::class);

        $menuConfig = [
            'order' => 1,
            'name' => 'Pages',
            'icon' => 'fas fa-home',
            'route' => 'pages',
            'activeFor' => ['pages', 'templates'],
            'children' => [
                (object) [ 'name' => 'Pages', 'route' => 'pages', 'activeFor' => ['pages']],
                (object) [ 'name' => 'Templates', 'route' => 'templates', 'activeFor' => ['templates'], 'max_user_level_id' => 1],
                (object) [ 'name' => 'Settings', 'route' => ['settings.index', 'pages'], 'activeFor' => ['settings']],
            ]
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);

        $this->mergeConfigFrom(__DIR__.'/../Config/pages.php', 'pages');
    }
}
