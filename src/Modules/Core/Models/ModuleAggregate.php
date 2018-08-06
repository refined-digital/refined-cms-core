<?php

namespace RefinedDigital\CMS\Modules\Core\Models;

class ModuleAggregate
{

    protected $routeFiles = [];


    /**
     * @param array $config
     *  @option required integer order
     *  @option required string name
     *  @option required string icon
     *  @option required string route
     *  @option required array activeFor
     *  @option array children (object) [ 'name' => 'Groups', 'route' => 'users', 'activeFor' => ['users']]
     *
     */
    public function addMenuItem($config)
    {
        $this->routeFiles[$config['order']] = json_decode(json_encode([
            'name'      => $config['name'],
            'icon'      => $config['icon'],
            'route'     => $config['route'],
            'activeFor' => $config['activeFor'],
            'children'  => isset($config['children']) && is_array($config['children']) ? $config['children'] : [],
        ]));
    }

    public function getMenuItems()
    {
        return $this->routeFiles;
    }

}
