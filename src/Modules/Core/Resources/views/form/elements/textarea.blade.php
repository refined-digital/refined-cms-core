{!!
    html()
        ->textarea($field->name)
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attribute('required', 'required')
!!}