<div class="form__group form__group--{{ forms()->getGroupCount($groups) }}">
    @foreach($groups as $field)
        @if (isset($field->count)) @continue @endif
        <div
            class="form__row{{ isset($field->required) && $field->required ? ' form__row--required' : '' }}{{ $errors->has($field->name) ? ' form__row--has-error' : '' }}"
            {!! (isset($field->row->attrs) ? help()->arrToAttr($field->row->attrs) : '') !!}
        >
            <?php
                $show = true;
                if (isset($field->type) && $field->type == 'datetime') {
                    $show = false;
                }

                if (isset($field->hideLabel) && $field->hideLabel) {
                    $show = false;
                }

                $attrs = [
                    'required' => 'required',
                ];
                if (isset($field->{'v-model'})) {
                    $attrs['v-model'] = $field->{'v-model'};
                }

                if (isset($field->attrs) && is_object($field->attrs)) {
                    $attrs = array_merge($attrs, (array) $field->attrs);
                }
            ?>

            @if($show)
                {!! html()->label($field->label)->class('form__label')->for('form--'.$field->name) !!}
            @endif

            @if( (isset($field->pre_note) && $field->pre_note))
                <div class="form__note">
                    @if (isset($field->pre_note) && $field->pre_note) {!! $field->pre_note !!} @endif
                </div>
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
                        If you are having trouble with images, <a href="https://www.iloveimg.com/photo-editor" target="_blank">visit this page</a> to create your image.
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</div>
