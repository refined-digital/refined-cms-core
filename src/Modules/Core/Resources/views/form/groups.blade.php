<div class="form__group form__group--{{ sizeof($groups) }}">
    @foreach($groups as $field)
        <div class="form__row{{ isset($field->required) && $field->required ? ' form__row--required' : '' }}{{ $errors->has($field->name) ? ' form__row--has-error' : '' }}">

            <?php
                $show = true;
                if (isset($field->type) && $field->type == 'datetime') {
                    $show = false;
                }

                if (isset($field->hideLabel) && $field->hideLabel) {
                    $show = false;
                }

            ?>

            @if($show)
                {!! html()->label($field->label)->class('form__label')->for('form--'.$field->name) !!}
            @endif

            @if (isset($field->type) && view()->exists('core::form.elements.'.$field->type))
                @include('core::form.elements.'.$field->type)
            @else
                @include('core::form.elements.default')
            @endif


            @if( (isset($field->note) && $field->note) || (isset($field->imageNote) && $field->imageNote))
                <div class="form__note">
                    @if (isset($field->note) && $field->note) {!! $field->note !!} @endif
                    @if (isset($field->imageNote) && $field->imageNote)
                        {!! $field->imageNote !!} <br/>
                        If you are having trouble with images, <a href="http://www.picresize.com/" target="_blank">visit this page</a> to create your image.
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>