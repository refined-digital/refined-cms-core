@if ($d->{$field->field})

    @if (is_object($d->{$field->field}))
        {{ $d->{$field->field}->setTimezone(config('products.orders.timezone'))->format('d/m/Y g:ia') }}
    @else
        {{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $d->{$field->field})->setTimezone(config('products.orders.timezone'))->format('d/m/Y g:ia') }}
    @endif

@endif
