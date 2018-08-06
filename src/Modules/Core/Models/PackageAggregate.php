<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

class PackageAggregate
{

    protected $packages = [];


    /**
     * @param string $name
     * @param string $model
     *
     */
    public function addPackage($name, $package)
    {
        $this->packages[$name] = $package;
    }

    public function getPackage($package)
    {
        if (isset($this->packages[$package])) {
            return $this->packages[$package];
        }

        return false;
    }

    public function hasPackage($package)
    {
        return isset($this->packages[$package]);
    }

}
