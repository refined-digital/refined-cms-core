<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use RefinedDigital\CMS\Modules\Core\Aggregates\PaymentGatewayAggregate;
use Str;

class PaymentGatewayHelper {

    protected $aggregate;

    public function __construct(PaymentGatewayAggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    public function getAll()
    {
        $data = $this->aggregate->getAllGateways();
        $gateways = [];

        foreach ($data as $name => $gateway) {
            $gateways[$name] = app($gateway);
        }

        return $gateways;
    }

    public function get($type)
    {
        $gateways = $this->getAll();
        if (isset($gateways[$type])) {
            return $gateways[$type];
        }

        foreach($gateways as $name => $gateway) {
            if (Str::slug($name) === $type) {
                return $gateway;
            }
        }

        return false;
    }
}
