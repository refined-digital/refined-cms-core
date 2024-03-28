@php
    $classes = [
      'page__block',
      'page__block--form',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <article>
        @include('templates.includes.content.heading')
        <div>
            @include('templates.includes.content.text')
            <div>
                @if (isset($content->form) && $content->form && function_exists('forms'))
                    {!! forms()->load($content->form)->render() !!}
                @endif
            </div>
        </div>
    </article>
</section>
