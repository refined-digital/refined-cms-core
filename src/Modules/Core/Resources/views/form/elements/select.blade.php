@php
  $options = (array) $field->options;
  foreach ($options as $index => $opt) {
      if (is_numeric(strpos($opt, 'session.'))) {
          $key = str_replace('session.', '', $opt);
          if (session()->has($key)) {
              $data = session()->get($key);
              unset($options[$index]);
              if (is_array($data)) {
                  foreach ($data as $id => $d) {
                      if (is_array($d)) {
                        $options[$d['id']] = $d['name'];
                      } else {
                        $options[$id] = $d;
                      }
                  }
              } else {
                  $options[$index] = $data;
              }
          }
      }
  }
@endphp
{!!
    html()
        ->select($field->name, $options)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}
