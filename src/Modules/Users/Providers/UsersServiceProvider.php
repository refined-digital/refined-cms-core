<?php

namespace RefinedDigital\CMS\Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;

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

        $this->publishes([
            __DIR__.'/../../../../config/users.php' => config_path('users.php'),
        ], 'users');
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
            ->addRouteFile('users', __DIR__.'/../Http/routes.php');

        $children = [
            (object) [ 'name' => 'Users', 'route' => 'users', 'activeFor' => ['users']],
            (object) [ 'name' => 'Groups', 'route' => 'user-groups', 'activeFor' => ['user-groups']],
        ];

        $menuConfig = [
            'order' => 999,
            'name' => 'Users',
            'icon' => 'fa fa-user',
            'route' => 'users',
            'activeFor' => ['users', 'user-groups'],
            'children' => $children
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
