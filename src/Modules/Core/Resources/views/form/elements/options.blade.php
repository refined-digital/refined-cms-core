<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = array_values(old($field->name));
    }
?>
<rd-form-options :value="{{ json_encode($value) }}" name="{{ $field->name }}"></rd-form-options>