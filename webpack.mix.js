const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.config.fileLoaderDirs.images = 'img';
mix.setPublicPath('assets');
mix
  .js('resources/js/main.js', 'js/')
  .js('resources/js/front-end/modules/FormBuilder.js', 'assets/js')
  .js('resources/js/front-end/plugins/FormValidate.js', 'assets/js')
  .sass('resources/sass/main.scss', 'css')
;

if (mix.inProduction()) {
  mix.version();
}

