<?php

namespace RefinedDigital\CMS\Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Aggregates\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Aggregates\Models\RouteAggregate;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('media', [
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
        app(RouteAggregate::class)
            ->addRouteFile('media', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 800,
            'name' => 'Media',
            'icon' => 'fas fa-images',
            'route' => 'media',
            'activeFor' => ['media'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
