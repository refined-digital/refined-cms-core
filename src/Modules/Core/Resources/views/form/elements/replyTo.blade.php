@php
  $attrs['v-model'] = 'form.reply';
  $values = [];
  if (function_exists('forms')) {
      $values = forms()->getReplyToOptions();
  }
@endphp
<div>
    {!!
        html()
            ->select($field->name.'_type', $values)
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
