<aside class="modal modal--form" data-type="contact-us">
    <div class="modal__inner">
        <div class="modal__form">
            @php
                $intro = settings()->getByKeyCode('[settings:pages:form_intro]');
            @endphp
            @if ($intro)
                <div class="modal__form-intro">
                    {!! nl2br($intro) !!}
                </div>
            @endif
            @if (function_exists('forms'))
                {!! forms()->load(1)->render() !!}
            @endif
        </div>
    </div>
</aside>
