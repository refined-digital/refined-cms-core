<?php
    $attrs = [
        'required' => 'required',
    ];
    if (isset($field->attrs) && is_object($field->attrs)) {
        $attrs = array_merge($attrs, (array) $field->attrs);
    }
?>
{!!
    html()
        ->input(isset($field->type) ? $field->type : 'text', $field->name)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}
@section('scripts')
@if (isset($field->attrs->{'v-model'}))
    <?php
        $value = '';
        if (isset($data->{ $field->name })) {
            $value = $data->{ $field->name };
        }
        if(old($field->name)) {
            $value = old($field->name);
        }
    ?>
    <script>
        window.app.{{ $field->attrs->{'v-model'} }} = '{{$value}}';
    </script>
@endif
@append