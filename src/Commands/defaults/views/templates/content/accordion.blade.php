@php
    $classes = [
      'page__block',
      'page__block--content',
      'page__block--bg-'.((isset($content->background_colour) && $content->background_colour) ? $content->background_colour : 'white'),
    ];
@endphp
<section class="{{ implode(' ', $classes) }}">
    <div class="holder">
        <article>
            @include('templates.content.includes.heading')
            @include('templates.content.includes.text')

            <div class="accordion">
                <div class="accordion__panel">
                    <header>
                        <div class="accordion__header">
                            <h2 class="heading heading">
                                Title
                            </h2>
                            <aside>
                                <span class="accordion__button--open">@svg('plus')</span>
                                <span class="accordion__button--close">@svg('minus')</span>
                            </aside>
                        </div>

                        <div class="accordion__content">
                            <div>
                                ....
                            </div>
                        </div>
                    </header>
                </div>
            </div>
        </article>
    </div>
</section>
