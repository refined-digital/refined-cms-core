<?php

namespace App\RefinedCMS\{{FullName}}\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;

class {{Name}}ServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('{{names}}', [
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
        app(CustomModuleRouteAggregate::class)
            ->addRouteFile('{{name}}', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 500,
            'name' => '{{FullName}}',
            'icon' => 'fas fa-cookie-bite',
            'route' => '{{name}}',
            'activeFor' => ['{{name}}'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
