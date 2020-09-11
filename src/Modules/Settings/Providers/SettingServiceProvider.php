<?php

namespace RefinedDigital\CMS\Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('settings', [
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
            ->addRouteFile('setting', __DIR__.'/../Http/routes.php');
    }
}
