<?php

namespace RefinedDigital\CMS\Modules\Core\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\AliasLoader;
use RefinedDigital\CMS\Commands\ConvertCpanel;
use RefinedDigital\CMS\Commands\CreateModule;
use RefinedDigital\CMS\Commands\InstallCMS;
use RefinedDigital\CMS\Commands\InstallDatabase;
use RefinedDigital\CMS\Commands\InstallSymLink;
use RefinedDigital\CMS\Modules\Core\Exceptions\Handler;
use RefinedDigital\CMS\Modules\Core\Http\Middleware\Admin;
use RefinedDigital\CMS\Modules\Core\Http\Middleware\UserLevel;
use RefinedDigital\CMS\Modules\Core\Http\ResourceRegistrar;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PackageAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PaymentGatewayAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PublicRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;

use Validator;


class CMSServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot(Router $router)
    {
        Schema::defaultStringLength(191);

        view()->addNamespace('core', [
            base_path().'/resources/views',
            __DIR__.'/../Resources/views',
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
            __DIR__.'/../../../../assets' => public_path('vendor/refined/core')
        ], 'public');

        if ($this->app->runningInConsole()) {

            $this->commands([
                CreateModule::class,
            ]);

            $publicPathDir = explode('/', public_path());
            $publicPath = $publicPathDir[sizeof($publicPathDir)-1];
            if ($publicPath === 'public') {
              $this->commands([
                ConvertCpanel::class
              ]);
            }

            if (env('APP_NAME') == 'Laravel') {
                $this->commands([
                    InstallCMS::class
                ]);
            }

            if (
                env('DB_DATABASE') == 'homestead' ||
                (\DB::connection()->getDatabaseName() && !\Schema::hasTable('users'))
            ) {
                $this->commands([
                    InstallDatabase::class
                ]);
            }

            if (!is_dir(public_path('vendor/refined/core'))) {
                $this->commands([
                    InstallSymLink::class
                ]);
            }
        }


        // register the custom middleware
        $router->aliasMiddleware('userLevel', UserLevel::class);
        $router->aliasMiddleware('admin', Admin::class);
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
        $this->app->singleton(CustomModuleRouteAggregate::class);
        $this->app->singleton(PublicRouteAggregate::class);
        $this->app->singleton(ModuleAggregate::class);
        $this->app->singleton(PackageAggregate::class);
        $this->app->singleton(PaymentGatewayAggregate::class);

        // override the error handler
        $this->app->singleton(ExceptionHandler::class, Handler::class);


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
