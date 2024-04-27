@if ($field->relation && isset($d->{$field->relation}->name))
    {{ $d->{$field->relation}->name }}
@endif
