

    <footer class="page__footer">
      <div class="footer__bottom">
        Copyright &copy; {{ date('Y') }} {{ config('app.name') }} All rights reserved
        {!! menu()->holder(2)->view('elements.nav-footer')->get($page) !!}
      </div>
    </footer>


    <script src="{{ asset('js/main.js?id='.uniqid()) }}"></script>
    @yield('scripts')
  </body>
</html>
