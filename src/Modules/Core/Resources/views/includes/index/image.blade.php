@php
$value = $d->{$field->field};
unset($img);
if($value) {
  $isSvg = files()->load($value)->url();
  if (is_numeric(strpos($isSvg, '.svg'))) {
    $img = $isSvg;
  } else {
    $img = image()->load($d->{$field->field})->width(80)->height(80)->string();
  }
}
@endphp

@if (isset($img) && $img)
  <img src="{{ $img }}" height="80"/>
@endif
