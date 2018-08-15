<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

class RouteAggregate
{

    protected $routeFiles = [];

    public function addRouteFile($name, $file)
    {
        $this->routeFiles[$name] = $file;
    }

    public function getRouteFiles()
    {
        return $this->routeFiles;
    }

}
