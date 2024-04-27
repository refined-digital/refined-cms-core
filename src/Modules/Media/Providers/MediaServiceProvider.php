<?php

namespace RefinedDigital\CMS\Modules\Media\Providers;

use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->addNamespace('media', [
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
            ->addRouteFile('media', __DIR__.'/../Http/routes.php');

        $menuConfig = [
            'order' => 800,
            'name' => 'Media',
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor"><path class="fa-primary" d="M96 96c0-35.3 28.7-64 64-64H512c35.3 0 64 28.7 64 64V320c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64V96zm280 32c-8 0-15.5 4-20 10.7l-56 84L282.7 201c-4.6-5.7-11.4-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h80 48H472c8.9 0 17-4.9 21.2-12.7s3.7-17.3-1.2-24.6l-96-144C391.5 132 384 128 376 128zM224 160a32 32 0 1 0 0-64 32 32 0 1 0 0 64z"/><path class="fa-secondary" opacity="0.4" d="M48 120c0-13.3-10.7-24-24-24S0 106.7 0 120V344c0 75.1 60.9 136 136 136H456c13.3 0 24-10.7 24-24s-10.7-24-24-24H136c-48.6 0-88-39.4-88-88V120zm348 18.7C391.5 132 384 128 376 128s-15.5 4-20 10.7l-56 84L282.7 201c-4.6-5.7-11.4-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h80 48H472c8.9 0 17-4.9 21.2-12.7s3.7-17.3-1.2-24.6l-96-144z"/></svg>',
            'route' => 'media',
            'activeFor' => ['media'],
        ];

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);
    }
}
