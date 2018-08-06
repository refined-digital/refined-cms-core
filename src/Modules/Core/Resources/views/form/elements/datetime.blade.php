<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }
?>
<rd-date-time-picker
    :field="{{ json_encode($field) }}"
    value="{{ $value }}"
></rd-date-time-picker>