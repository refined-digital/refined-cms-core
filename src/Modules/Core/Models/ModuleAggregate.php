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

    public function addSubMenuItem($parent, $config)
    {
        $menuItem = $this->getMenuItem($parent);
        if ($menuItem) {
            $item = $menuItem->item;
            if (sizeof($item->children) < 1) {
                // add the main menu to the child menu
                $name = $item->name;
                if ($name == 'Blog') $name = 'Posts';

                $item->children[] = (object) [ 'name' => $name, 'route' => $item->route, 'activeFor' => $item->activeFor];
            }
            $item->children[] = (object) $config;

            // update the active for
            $item->activeFor = array_merge($item->activeFor, $config['activeFor']);

            $this->routeFiles[$menuItem->key] = $item;
        }
    }

    public function getMenuItems()
    {
        $routes = $this->routeFiles;
        ksort($routes);
        return $routes;
    }

    private function getMenuItem($item)
    {
        $items = $this->getMenuItems();
        if (is_array($items) && sizeof($items)) {
            foreach ($items as $key => $itm) {
                if ($itm->name == $item) {
                    return (object) ['key' => $key, 'item' => $itm];
                }
            }
        }

        return false;
    }
}
