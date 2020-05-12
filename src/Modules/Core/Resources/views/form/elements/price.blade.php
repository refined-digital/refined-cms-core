{!!
    html()
        ->input('number', $field->name)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
        ->attribute('inputmode', 'decimal')
!!}
@section('scripts')
@if (isset($field->attrs->{'v-model'}))
    @php
        $value = '';
        if (isset($data->{ $field->name })) {
            $value = $data->{ $field->name };
        }
        if(old($field->name)) {
            $value = old($field->name);
        }
    @endphp
@endif
@append
