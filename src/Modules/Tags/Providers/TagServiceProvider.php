<?php

namespace RefinedDigital\CMS\Modules\Tags\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

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
            __DIR__.'/../Resources/views',
            app_path().'/views'
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
            ->addRouteFile('tags', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 998,
            'name' => 'Tags / Categories',
            'icon' => 'fa fa-tags',
            'route' => 'tags',
            'activeFor' => ['tags']
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
