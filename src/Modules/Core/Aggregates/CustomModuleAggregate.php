<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class CustomModuleAggregate
{

    protected $modules = [];

    public function add(
        string $name,
        string $routes,
        array $menuConfig,
        ?string $sitemapModel,
        ?string $sitemapBasePage,
    )
    {
        app(CustomModuleRouteAggregate::class)
            ->addRouteFile($name, $routes);

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);

        if (isset($sitemapModel)) {
            app(SitemapXMLAggregate::class)
                ->add($name, $sitemapModel, $sitemapBasePage);
        }
    }
}
