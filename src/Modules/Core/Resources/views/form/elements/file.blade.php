<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }
?>
<rd-file value="{{ $value }}" name="{{ $field->name }}"></rd-file>