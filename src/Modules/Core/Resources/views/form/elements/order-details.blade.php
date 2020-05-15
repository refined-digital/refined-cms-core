@php
  $repo = new \RefinedDigital\ProductManager\Module\Http\Repositories\OrderRepository();
  echo str_replace('<h3>Order Details</h3>', '', $repo->generateOrderDetailsHtml($data));
@endphp
