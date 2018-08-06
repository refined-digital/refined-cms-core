<?php

namespace RefinedDigital\CMS\Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

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
            ->addRouteFile('media', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 88,
            'name' => 'Media',
            'icon' => 'fas fa-images',
            'route' => 'media',
            'activeFor' => ['media'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
