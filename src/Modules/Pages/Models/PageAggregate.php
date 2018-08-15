<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

class PageAggregate
{

    protected $modules = [];


    /**
     * @param string $name
     * @param string $model
     *
     */
    public function addModule($name, $module)
    {
        $this->modules[$name] = $module;
    }

    public function getModule($module)
    {
        if (isset($this->modules[$module])) {
            return $this->modules[$module];
        }

        return false;
    }

    public function hasModule($module)
    {
        return isset($this->modules[$module]);
    }

    public function all()
    {
        return $this->modules;
    }

}
