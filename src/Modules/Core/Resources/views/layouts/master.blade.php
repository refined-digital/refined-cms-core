@include('core::includes.header')
        <div id="app" class="app__holder" :class="{ 'app--has-media' : media.active, 'app--has-sitemap' : sitemap.active }">

            <div class="loader" v-show="loading"><div class="spinner"></div></div>

            <div class="app__left">
                <figure class="app__logo">
                    <a href="{{ config('app.url') }}/refined"><img src="{{ asset('vendor/refinedcms/img/logos/admin-small-logo.png') }}"/></a>
                </figure>
                <nav class="app__nav">
                    @if(is_array($menu) && sizeof($menu))
                    <ul>
                        @foreach($menu as $item)
                        <li class="app__nav-item{{ in_array($activeModule, $item->activeFor) ? ' app__nav-item--active' : '' }}">
                            <a href="{{ route('refined.'.$item->route.'.index') }}">
                                <i class="{{ $item->icon }}"></i>
                                <span>{{ $item->name }}</span>
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
                        <a href="{{ config('app.url') }}" target="_blank">View Website</a>
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
                <rd-media :modal="true"></rd-media>
                <rd-sitemap></rd-sitemap>
            @endif

        </div><!-- / app holder -->

        <script src="{{ asset('vendor/refinedcms/js/main.js?v='.uniqid()) }}"></script>
        <script>
            window.app.siteUrl = "{{ rtrim(config('app.url'), '/') }}";
            window.app.user = JSON.parse('{!! json_encode(users()->getLoggedInUser()) !!}');
        </script>
        @yield('scripts')
    </body>
</html>