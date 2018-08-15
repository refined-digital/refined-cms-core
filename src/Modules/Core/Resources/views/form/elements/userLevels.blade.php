{!!
    html()
        ->select($field->name, users()->getUserLevelsForSelect())
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}