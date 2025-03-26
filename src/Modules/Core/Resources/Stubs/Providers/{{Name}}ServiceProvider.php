<?php

namespace App\RefinedCMS\{{FullName}}\Providers;

use App\RefinedCMS\{{FullName}}\Models\{{Name}};
use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleAggregate;
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
        $config = [
            'order' => 500,
            'name' => '{{FullName}}',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor"><path class="fa-secondary" opacity=".4" d="M257.5 27.6c-.8-5.4-4.9-9.8-10.3-10.6v0c-22.1-3.1-44.6 .9-64.4 11.4l-74 39.5C89.1 78.4 73.2 94.9 63.4 115L26.7 190.6c-9.8 20.1-13 42.9-9.1 64.9l14.5 82.8c3.9 22.1 14.6 42.3 30.7 57.9l60.3 58.4c16.1 15.6 36.6 25.6 58.7 28.7l83 11.7c22.1 3.1 44.6-.9 64.4-11.4l74-39.5c19.7-10.5 35.6-27 45.4-47.2l36.7-75.5c9.8-20.1 13-42.9 9.1-64.9v0c-.9-5.3-5.3-9.3-10.6-10.1c-51.5-8.2-92.8-47.1-104.5-97.4c-1.8-7.6-8-13.4-15.7-14.6c-54.6-8.7-97.7-52-106.2-106.8zM208 144a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM144 336a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm224-64a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/><path class="fa-primary" d="M176 176a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM144 336a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm224-64a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/></svg>',
            'route' => '{{name}}',
            'activeFor' => ['{{name}}'],
        ];


        app(CustomModuleAggregate::class)
            ->add(
                name: '{{name}}',
                routes: __DIR__.'/../Http/routes.php',
                menuConfig: $config,
                model: {{Name}}::class,
                basePage: '{{name}}'
            );
    }
}
