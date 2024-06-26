<?php

namespace RefinedDigital\CMS\Modules\Tags\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;

class TagServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('tags', [
            base_path().'/resources/views',
            __DIR__.'/../Resources/views',
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (help()->isMultiTenancy()) {
            return;
        }

        app(RouteAggregate::class)
            ->addRouteFile('tags', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 998,
            'name' => 'Tags / Categories',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path class="fa-secondary" opacity="0.4" d="M311.1 38.9c9.4-9.3 24.6-9.2 33.9 .2L472.8 168.4c52.4 53 52.4 138.2 0 191.2L360.8 472.9c-9.3 9.4-24.5 9.5-33.9 .2s-9.5-24.5-.2-33.9L438.6 325.9c33.9-34.3 33.9-89.4 0-123.7L310.9 72.9c-9.3-9.4-9.2-24.6 .2-33.9z"/><path class="fa-primary" d="M0 80V229.5c0 17 6.7 33.3 18.7 45.3l168 168c25 25 65.5 25 90.5 0L410.7 309.3c25-25 25-65.5 0-90.5l-168-168c-12-12-28.3-18.7-45.3-18.7H48C21.5 32 0 53.5 0 80zm112 32a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>',
            'route' => 'tags',
            'activeFor' => ['tags']
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
