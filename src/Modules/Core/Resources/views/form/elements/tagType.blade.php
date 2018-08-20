{!!
    html()
        ->select($field->name, tags()->getTypes())
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}