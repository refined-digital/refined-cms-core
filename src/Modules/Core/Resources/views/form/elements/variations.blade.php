<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name) && is_array(old($field->name))) {
        $value = array_values(old($field->name));
    }

?>
<rd-product-variations
  :value="{{ json_encode($value) }}"
  :field="{{ json_encode($field) }}"
  :item="{{ json_encode($field) }}"
  :variations="{{ json_encode(productVariations()->getForFront()) }}"
  :types="{{ json_encode(productVariations()->getForSelect()) }}"
  :statuses="{{ json_encode(products()->getStatusesForView()) }}"
  name="{{ $field->name }}"
></rd-product-variations>
