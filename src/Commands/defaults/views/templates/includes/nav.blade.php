<nav class="page__nav">
    {!! menu()->holder('pages')->get($page) !!}
    <a href="#" class="modal__trigger--button fade-in" data-type="menu">
        <span class="modal__trigger--menu">
            @svg('bars')
        </span>
        <span class="modal__trigger--close">
            @svg('close')
        </span>
    </a>
</nav>
