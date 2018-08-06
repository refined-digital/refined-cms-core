<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }
    if(old($field->name)) {
        $value = old($field->name);
    }
?>
<rd-rich-text name="{{ $field->name }}" id="{{ 'form--'.$field->name }}" :content="{{ json_encode($value) }}"></rd-rich-text>