<?php
    $value = null;
    if (old($field->name)) {
        $value = old($field->name);
    }
?>
<input type="password" name="{{ $field->name }}" id="form--{{ $field->name }}" required value="{{ $value }}"{!! (isset($field->attrs) ? help()->arrToAttr($field->attrs) : '') !!} class="form__control"/>