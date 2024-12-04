<?php
    $value = '';
    if (isset($data->{ $field->name })) {
        $value = $data->{ $field->name };
    }

    if(old($field->name)) {
        $value = old($field->name);
    }

    $settings = 'undefined';
    if (isset($field->settings) && $field->settings) {
        $settings = json_encode($field->settings);
    }
?>
<rd-link value="{{ $value }}" name="{{ $field->name }}" :settings="{{ $settings }}"></rd-link>
