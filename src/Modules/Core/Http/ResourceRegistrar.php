<?php

namespace RefinedDigital\CMS\Modules\Core\Http;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class ResourceRegistrar extends OriginalRegistrar {

    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = ['index', 'create', 'store', 'edit', 'update', 'destroy', 'position', 'duplicate'];


    /**
     * Add the position method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourcePosition($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/position';

        $action = $this->getResourceAction($name, $controller, 'position', $options);

        return $this->router->post($uri, $action);
    }

    protected function addResourceDuplicate($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/{'.$base.'}/duplicate';

        $action = $this->getResourceAction($name, $controller, 'duplicate', $options);

        return $this->router->get($uri, $action);
    }
}
