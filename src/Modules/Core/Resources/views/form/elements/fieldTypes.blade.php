@php
  $values = [];
  if (function_exists('forms')) {
      $values = forms()->getForSelect('field types');
  }
@endphp
{!!
    html()
        ->select($field->name, $values)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}
