<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

class CustomModuleRouteAggregate
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
