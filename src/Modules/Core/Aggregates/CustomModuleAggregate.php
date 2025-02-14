<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class CustomModuleAggregate
{

    protected $sitemap = [];

    public function add(
        string $name,
        string $routes,
        array $menuConfig,
        ?string $model,
        ?string $basePage,
    )
    {
        app(CustomModuleRouteAggregate::class)
            ->addRouteFile($name, $routes);

        app(ModuleAggregate::class)
            ->addMenuItem($menuConfig);

        if (isset($model)) {
            app(SitemapXMLAggregate::class)
                ->add($name, $model, $basePage);

            $this->sitemap[$model] = $basePage;
        }
    }

    public function getSitemapBasePage(string $name)
    {
        return $this->sitemap[$name] ?? null;
    }
}
