<?php

namespace RefinedDigital\CMS\Modules\SiteSettings\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;

class SiteSettingsServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        app(CustomModuleRouteAggregate::class)
            ->addRouteFile('site-settings', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 2,
            'name' => 'Site Settings',
            'heading' => 'Site Settings',
            'icon' => 'fas fa-palette',
            'route' => ['settings.index', 'site-settings'],
            'activeFor' => ['site-settings'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
