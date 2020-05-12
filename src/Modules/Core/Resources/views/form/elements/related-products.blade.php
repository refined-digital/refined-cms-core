<?php
  // todo: move this into the product manager directory

  $value = '';

  if (isset($data->related_products) && $data->related_products->count()) {
      $v = [];
      foreach ($data->related_products as $product) {
          $v[] = $product->id;
      }
      $value = join(',', $v);
  }

  if(old('related_products')) {
      $value = old('related_products');
  }

  $dataSet = products()->getForSelect();
  $choices = [];
  foreach ($dataSet as $option) {
      if (!isset($data->id) || (isset($data->id) && $data->id !== $option['id'])) {
        $choices[] = $option;
      }
  }
?>
<rd-select-as-tags
  :field="{{ json_encode($field) }}"
  :values="{{ json_encode($value) }}"
  :choices="{{ json_encode($choices) }}"
></rd-select-as-tags>
