<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = array_values(old($field->name));
    }
?>
<rd-form-repeatable :value="{{ json_encode($value) }}" name="{{ $field->name }}" :item="{{ json_encode($field) }}"></rd-form-repeatable>