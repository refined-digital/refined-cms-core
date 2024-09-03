@php
    use RefinedDigital\CMS\Modules\Core\Aggregates\AssetAggregate;
    app(AssetAggregate::class)->addStyle('form.css');

    $classes = [
      'page__block',
      'page__block--form',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <article class="holder__body">
        @include('templates.content.includes.heading')
        @include('templates.content.includes.content')
        @if (isset($content->form) && $content->form && function_exists('forms'))
            {!! forms()->load($content->form)->render() !!}
        @endif
    </article>
</section>
