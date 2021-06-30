<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }
?>
<rd-image value="{{ $value }}" name="{{ $field->name }}"></rd-image>
