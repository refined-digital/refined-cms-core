<?php

namespace RefinedDigital\CMS\Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

class UsersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('users', [
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
            ->addRouteFile('users', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 999,
            'name' => 'Users',
            'icon' => 'fa fa-user',
            'route' => 'users',
            'activeFor' => ['users']
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
