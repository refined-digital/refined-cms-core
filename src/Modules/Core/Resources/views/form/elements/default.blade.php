{!!
    html()
        ->input(isset($field->type) ? $field->type : 'text', $field->name)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
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
    <script>
        window.app.{{ $field->attrs->{'v-model'} }} = '{!! addslashes($value) !!}';
    </script>
@endif
@append