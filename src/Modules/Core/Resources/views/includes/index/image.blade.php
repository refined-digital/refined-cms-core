@if ( $d->{$field->field} )
{!! image()->load($d->{$field->field})->width(80)->height(80)->get() !!}
@endif