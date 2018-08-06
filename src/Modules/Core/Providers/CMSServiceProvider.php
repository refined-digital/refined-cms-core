<?php

namespace RefinedDigital\CMS\Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\AliasLoader;
use RefinedDigital\CMS\Commands\InstallCMS;
use RefinedDigital\CMS\Commands\InstallDatabase;
use RefinedDigital\CMS\Commands\InstallSymLink;
use RefinedDigital\CMS\Modules\Core\Http\ResourceRegistrar;
use RefinedDigital\CMS\Modules\Core\Models\PackageAggregate;
use Validator;

use RefinedDigital\CMS\Modules\Core\Models\RouteAggregate;
use RefinedDigital\CMS\Modules\Core\Models\ModuleAggregate;


class CMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        Schema::defaultStringLength(191);

        view()->addNamespace('core', [
            __DIR__.'/../Resources/views',
            app_path().'/views'
        ]);

        view()->composer(
			'core::layouts.master', 'RefinedDigital\CMS\Modules\Core\Http\ViewComposers\MenuComposer'
        );

        Validator::extend('not0', function($attribute, $value) {
            return $value > '0';
        });


        // load in the routes
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        // load in the option for assets
        $this->publishes([
            __DIR__.'/../../../../assets' => public_path('vendor/refinedcms')
        ], 'public');

        if ($this->app->runningInConsole()) {
            if (env('APP_NAME') == 'Laravel') {
                $this->commands([
                    InstallCMS::class
                ]);
            }

            if (env('DB_DATABASE') == 'homestead') {
                $this->commands([
                    InstallDatabase::class
                ]);
            }

            if (!is_dir(public_path().'/vendor/refinedcms')) {
                $this->commands([
                    InstallSymLink::class
                ]);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // override the route registrar
        $registrar = new ResourceRegistrar($this->app['router']);
        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });


        // load in the helpers
        $this->app->singleton(RouteAggregate::class);
        $this->app->singleton(ModuleAggregate::class);
        $this->app->singleton(PackageAggregate::class);

        // load in the modules
        $this->mergeConfigFrom(__DIR__.'/../../../../config/modules.php', 'modules');
        $loader = AliasLoader::getInstance();

        $modules = config('modules');
        if (is_array($modules) && sizeof($modules)) {
            foreach($modules as $module) {
                if (isset($module['Provider']) && $module['Provider']) {
                    $this->app->register($module['Provider']);
                }

                if (isset($module['Aliases']) && sizeof($module['Aliases'])) {
                    foreach($module['Aliases'] as $alias) {
                        $loader->alias($alias['name'], $alias['path']);
                    }
                }
            }
        }
    }
}
