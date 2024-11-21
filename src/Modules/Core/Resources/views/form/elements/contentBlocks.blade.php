@php
  $value = [];

  if (isset($data->{ $field->name })) {
      $value = json_decode($data->{ $field->name });
  }

  if(old($field->name)) {
      $value = json_decode(old($field->name));
  }

  $page = new stdClass();
  $page->{$field->name} = $value;

@endphp

<rd-content-blocks
  :config="{{ json_encode(pages()->getConfig()) }}"
  :page="{{ json_encode($page) }}"
  name="{{ $field->name }}"
  :canShowAnchors="false"
/>

