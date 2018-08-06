@if (isset($field->options[$d->{$field->field}]))
    {{ $field->options[$d->{$field->field}] }}
@endif