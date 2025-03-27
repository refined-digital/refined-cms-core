@php
  $value = [];

  $content = app(\RefinedDigital\CMS\Modules\Core\Aggregates\ContentAggregate::class)->getForConfig();

  if (isset($data->{ $field->name })) {
      $value = $data->{ $field->name };
  }

  if(old($field->name)) {
      $value = json_decode(old($field->name));
  }

  $page = new stdClass();
  $page->{$field->name} = $value;
@endphp

<rd-content-blocks
  :content="{{ json_encode($content) }}"
  :page="{{ json_encode($page) }}"
  name="{{ $field->name }}"
  :canShowAnchors="false"
/>

