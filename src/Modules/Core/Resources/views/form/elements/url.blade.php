<div class="form__control--url">
    <span>
        {{ rtrim(config('app.url'), '/') }}/
    </span>
    {!!
        html()
            ->text($field->name)
            ->class('form__control')
            ->id('form--'.$field->name)
            ->attribute('required', 'required')
            ->attribute('readonly', '')
            ->attribute('v-model', 'content.uri')
    !!}
    <span class="copy-url" @click="copyUrl"><i class="fas fa-link"></i></span>
</div>
@section('scripts')
<script>
    <?php
        $value = null;
        if (isset($data->meta->uri)) {
            $value = $data->meta->uri;
        }
        if(old($field->name)) {
            $value = old($field->name);
        }

    ?>window.app.content.uri = "{{ $value }}";
</script>
@append
