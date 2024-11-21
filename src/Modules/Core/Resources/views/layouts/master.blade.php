@include('core::includes.header')
@php
  $bits = explode('.', Route::currentRouteName());
  $module = $bits[$activeOffset ?? 1];
  $siteUrl = help()->getSiteUrl();
  $publicUrl = help()->getPublicUrl();
@endphp
        <div id="app" class="app__holder app__module--{{ $module }}" :class="{ 'app--has-media' : media.active, 'app--has-media' : media.showModal, 'app--has-sitemap' : sitemap.active, 'app--has-link' : link.active }">

            <div class="loader" v-show="loading"><div class="spinner"></div></div>

            <div class="app__left">
                <figure class="app__logo">
                    <a href="{{ $siteUrl }}/refined"><img src="{{ refined_asset('vendor/refined/core/img/logos/admin-small-logo.png') }}"/></a>
                </figure>
                <nav class="app__nav">
                    @if(is_array($menu) && sizeof($menu))
                    <ul>
                        @foreach($menu as $item)
                        <li class="app__nav-item{{ in_array($activeModule, $item->activeFor) ? ' app__nav-item--active' : '' }}">
                            <a href="{{ is_array($item->route) ? route('refined.'.$item->route[0], $item->route[1]) : route('refined.'.$item->route.'.index') }}">
                                <span class="app__nav-item--icon">{!! $item->icon !!}</span>
                                <span class="app__nav-item--title">{{ $item->name }}</span>
                            </a>
                            @if (is_array($item->children) && sizeof($item->children) && in_array($activeModule, $item->activeFor))
                            <ul>
                                @foreach($item->children as $child)
                                    <?php
                                        $active = in_array($activeModule, $child->activeFor);
                                        if (isset($settings)) {
                                            $active = false;
                                            if (in_array('settings', $child->activeFor)) {
                                                $active = true;
                                            }
                                        }
                                    ?>
                                <li class="app__nav-sub-item{{ $active ? ' app__nav-sub-item--active' : '' }}">
                                    <a href="{{ is_array($child->route) ? route('refined.'.$child->route[0], $child->route[1]) : route('refined.'.$child->route.'.index') }}">
                                        {{ $child->name }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </nav>
            </div><!-- / app left -->
            <div class="app__right">
                <header class="app__header">
                    @include('core::includes.search')
                    <div class="app__profile">
                        Welcome back {{ auth()->user()->first_name }}
                        <span> | </span>
                        <a href="{{ $publicUrl }}" target="_blank">View Website</a>
                        <span> | </span>
                        <a href="{{ route('logout') }}">Logout</a>
                    </div>
                </header>
                <div class="app__body">
                    @include('core::includes.body-header')

                    @include('core::includes.errors')

                    @yield('template')
                </div>
            </div><!-- / right -->

            @if($activeModule != 'media')
                <rd-media :modal="true" :max-filesize="{{ help()->getUploadMaxFilesize() }}"></rd-media>
                <rd-sitemap></rd-sitemap>
            @endif

        </div><!-- / app holder -->

        <script>
          window.siteUrl = "{{ $siteUrl }}";
          window.publicUrl = "{{ $publicUrl }}";
        </script>
        <script src="{{ refined_asset('vendor/refined/core/js/main.js?v='.uniqid()) }}"></script>
        <script>
            window.app.richEditor = {!! json_encode(config('rich-editor')) !!}
            window.app.siteUrl = "{{ $siteUrl }}";
            window.app.publicUrl = "{{ $publicUrl }}";
            window.app.user = {!! json_encode(users()->getLoggedInUser()) !!}
        </script>
        @yield('scripts')
    </body>
</html>
