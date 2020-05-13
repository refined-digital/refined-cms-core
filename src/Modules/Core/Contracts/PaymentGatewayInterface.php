<?php

namespace RefinedDigital\CMS\Modules\Core\Contracts;

interface PaymentGatewayInterface {

    public function getView();

    public function process($request, $cart);

}
