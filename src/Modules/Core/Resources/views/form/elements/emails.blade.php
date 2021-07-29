<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }

?>
<rd-form-email
  :field="{{ json_encode($field) }}"
  :value="{{ json_encode($value) }}"
></rd-form-email>
