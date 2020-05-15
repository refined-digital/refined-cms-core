@if (function_exists('orders'))
  {{ orders()->getStatus($d->{$field->field}) }}
@else
  {{ $d->{$field->field} }}
@endif
