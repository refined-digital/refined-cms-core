@php
  $value = '';
  if (isset($data->{ $field->name })) {
      $value = $data->{ $field->name };
  }
  if(old($field->name)) {
      $value = old($field->name);
  }
@endphp

<rd-colour-picker
  :field="{{ json_encode($field) }}"
  value="{{ $value }}"
></rd-colour-picker>
