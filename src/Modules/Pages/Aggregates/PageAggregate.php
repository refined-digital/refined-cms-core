<?php

namespace RefinedDigital\CMS\Modules\Pages\Aggregates;

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
        if (isset($module['fields']) && is_array($module['fields']) && sizeof($module['fields'])) {
            $fields = [];

            foreach ($module['fields'] as $field) {
                if (!isset($field['index'])) {
                    $field['index'] = str_slug($field['name'],'_');
                }
                $fields[] = $field;
            }

            if (sizeof($fields)) {
                $module['fields'] = $fields;
            }
        }
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
