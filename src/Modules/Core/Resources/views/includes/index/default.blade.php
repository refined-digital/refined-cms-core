@php
  $search = ['[colour]', '[/colour]'];
  $replace = ['', ''];
@endphp
{{ str_replace($search, $replace, $d->{$field->field}) }}
