<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

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
        $data = json_decode(json_encode([
            'name'              => $config['name'],
            'heading'           => $config['heading'] ?? $config['name'],
            'icon'              => $config['icon'],
            'route'             => $config['route'],
            'activeFor'         => $config['activeFor'],
            'max_user_level_id' => $config['max_user_level_id'] ?? null,
            'children'          => isset($config['children']) && is_array($config['children']) ? $config['children'] : [],
        ]));

        $order = $config['order'];
        if (isset($this->routeFiles[$order])) {
            $suffix = 1;
            while (isset($this->routeFiles[$order .'.'. $suffix])) {
                $suffix++;
            }
            $order = $order.'.'.$suffix;
        }

        $orderOverride = config('refined-menu.order');
        if (isset($orderOverride[$config['name']]) && $orderOverride[$config['name']]) {
            $order = $orderOverride[$config['name']];
        }

        $this->routeFiles[$order] = $data;
        foreach ($data->children as $child) {
            if (gettype($child->route) == 'array' && is_array($child->route) && isset($child->route[1]) && gettype($child->route[1]) == 'object') {
                $child->route[1] = (array) $child->route[1];
            }
        }
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

    /**
     * Sub links of a module's menu item (e.g. Pages -> Templates) as
     * [{name, url}], honouring the menu's user level gate. Used by the
     * full-screen workspaces, which cover the admin sidebar.
     */
    public function getMenuChildrenLinks(string $route): array
    {
        $user = auth()->user();
        $links = [];

        foreach ($this->getMenuItems() as $item) {
            if (!is_string($item->route ?? null) || $item->route !== $route || empty($item->children)) {
                continue;
            }

            foreach ($item->children as $child) {
                if (is_string($child->route) && $child->route === $route) {
                    continue;
                }
                if (isset($child->max_user_level_id) && $user && $user->user_level_id > $child->max_user_level_id) {
                    continue;
                }

                $links[] = [
                    'name' => $child->name,
                    'url' => is_array($child->route)
                        ? route('refined.'.$child->route[0], $child->route[1] ?? [])
                        : route('refined.'.$child->route.'.index'),
                ];
            }
        }

        return $links;
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
