@php
  $search = ['[colour]', '[/colour]'];
  $replace = ['', ''];

  $name = $d->{$field->field};

  if (strpos($field->field, '.')) {
    $parts = explode('.', $field->field);
    $name = $d->{$parts[0]}->{$parts[1]};
  }

@endphp
{{ str_replace($search, $replace, $name) }}
