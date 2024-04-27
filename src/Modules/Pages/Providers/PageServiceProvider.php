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
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor"><path class="fa-secondary" opacity=".4" d="M64 270.5L64.1 472c0 22.1 17.9 40 40 40H184c22.1 0 40-17.9 40-40V383.7c0-17.7 14.3-32 32-32h64c17.7 0 32 14.3 32 32V472c0 22.1 17.9 40 40 40h80.5c22.1 0 40-18 40-40.1l-.4-201.3L288 74.5 64 270.5z"/><path class="fa-primary" d="M266.9 7.9C279-2.6 297-2.6 309.1 7.9l256 224c13.3 11.6 14.6 31.9 3 45.2s-31.9 14.6-45.2 3L288 74.5 53.1 280.1c-13.3 11.6-33.5 10.3-45.2-3s-10.3-33.5 3-45.2l256-224z"/></svg>',
            'route' => 'pages',
            'activeFor' => ['pages', 'templates'],
            'children' => [
                (object) [ 'name' => 'Pages', 'route' => 'pages', 'activeFor' => ['pages']],
                (object) [ 'name' => 'Templates', 'route' => 'templates', 'activeFor' => ['templates'], 'max_user_level_id' => 1],
                (object) [ 'name' => 'Settings', 'route' => ['settings.index', ['type' => 'pages']], 'activeFor' => ['settings']],
            ]
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);

        $this->mergeConfigFrom(__DIR__.'/../Config/pages.php', 'pages');
    }
}
