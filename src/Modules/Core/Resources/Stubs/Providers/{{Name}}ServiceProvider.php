<?php

namespace App\RefinedCMS\{{FullName}}\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Models\CustomModuleRouteAggregate;

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
        app(CustomModuleRouteAggregate::class)
            ->addRouteFile('blog', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 200,
            'name' => '{{FullName}}',
            'icon' => 'fas fa-cookie-bite',
            'route' => '{{name}}',
            'activeFor' => ['{{name}}'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
