<?php
    $value = '';
    if (isset($data->modelTags)) {
        $value = $data->modelTags;
    }
    if(old('modelTags')) {
        $value = old('modelTags');
    }
?>
<rd-tags :field="{{ json_encode($field) }}" :values="{{ json_encode($value) }}" type="{{ $field->tagType }}"></rd-tags>