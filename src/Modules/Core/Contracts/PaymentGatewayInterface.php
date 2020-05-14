<?php

namespace RefinedDigital\CMS\Modules\Core\Contracts;

interface PaymentGatewayInterface {

    public function process($request, $form, $emailData);

}
