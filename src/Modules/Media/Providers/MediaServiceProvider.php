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
            base_path().'/resources/views'
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
