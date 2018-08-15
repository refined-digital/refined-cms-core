<div>
    <?php
        $attrs['v-model'] = 'form.reply';
    ?>
    {!!
        html()
            ->select($field->name.'_type', forms()->getReplyToOptions())
            ->class('form__control')
            ->id('form--'.$field->name.'-type')
            ->attributes($attrs)
    !!}
    <?php
        unset($attrs['v-model']);
        $attrs['v-if'] = 'form.reply == \'text\'';
    ?>
    {!!
        html()
            ->input('text', $field->name)
            ->class('form__control')
            ->id('form--'.$field->name)
            ->attributes($attrs)
    !!}
</div>
