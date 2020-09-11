<?php

namespace RefinedDigital\CMS\Modules\Core\Aggregates;

class PaymentGatewayAggregate
{

    protected $gateways = [];


    /**
     * @param string $name
     * @param string $model
     *
     */
    public function addGateway($name, $package)
    {
        $this->gateways[$name] = $package;
    }

    public function getGateway($package)
    {
        if (isset($this->gateways[$package])) {
            return $this->gateways[$package];
        }

        return false;
    }

    public function hasGateway($package)
    {
        return isset($this->gateways[$package]);
    }

    public function getAllGateways()
    {
        return $this->gateways;
    }

}
