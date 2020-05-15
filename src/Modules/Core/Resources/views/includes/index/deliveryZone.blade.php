@if (function_exists('orders'))
  {{ orders()->getDeliveryZone($d->{$field->field}) }}
@else
  {{ $d->{$field->field} }}
@endif
