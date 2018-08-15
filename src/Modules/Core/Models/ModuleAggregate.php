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
            'name'              => $config['name'],
            'icon'              => $config['icon'],
            'route'             => $config['route'],
            'activeFor'         => $config['activeFor'],
            'max_user_level_id' => isset($config['max_user_level_id']) ? $config['max_user_level_id'] : null,
            'children'          => isset($config['children']) && is_array($config['children']) ? $config['children'] : [],
        ]));
    }

    public function getMenuItems()
    {
        $routes = $this->routeFiles;
        ksort($routes);
        return $routes;
    }

}
