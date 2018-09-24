<?php

namespace RefinedDigital\CMS\Modules\Settings\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;

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
            ->addRouteFile('setting', __DIR__.'/../Http/routes.php');
    }
}
