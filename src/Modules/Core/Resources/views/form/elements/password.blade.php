<?php
    $value = null;
    if (old($field->name)) {
        $value = old($field->name);
    }
?>
<input type="password" name="{{ $field->name }}" id="form--{{ $field->name }}" required value="{{ $value }}" class="form__control"/>