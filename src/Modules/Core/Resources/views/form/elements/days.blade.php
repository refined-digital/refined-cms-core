@php
  $days = help()->getDaysOfWeek();

  $checked = [];
  $value = isset($data->available_days) ? $data->available_days : [];
  for ($i=1; $i<=7; $i++) {
      if (in_array($i, $value)) {
          $checked[] = $i;
      }
  }
@endphp
@foreach ($days as $dayKey => $dayName)
  <div>
    {!!
        html()
            ->checkbox($field->name.'[]')
            ->value($dayKey)
            ->checked(in_array($dayKey, $checked))
            ->id('form--'.$field->name.'-'.$dayKey)
            ->attributes($attrs)
    !!}
    {!!
        html()
            ->label($dayName, 'form--'.$field->name.'-'.$dayKey)
    !!}
  </div>
@endforeach
