@php
  $repo = new \RefinedDigital\ProductManager\Module\Http\Repositories\OrderRepository();
  echo str_replace('<h3>Billing Details</h3>', '', $repo->generateBillingDetailsHtml($data))
@endphp
