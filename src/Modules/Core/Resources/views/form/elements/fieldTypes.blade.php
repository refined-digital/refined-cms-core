{!!
    html()
        ->select($field->name, forms()->getForSelect('field types'))
        ->class('form__control')
        ->id('form--'.$field->name)
        ->attributes($attrs)
!!}