const mix = require('laravel-mix');
const webpack = require('webpack');
const path = require('path');

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

mix.options({
    fileLoaderDirs: {
      images: `img`,
    }
  })
  .setPublicPath('assets')
  .copyDirectory('resources/img', 'assets/img')
  .copy('resources/svg/editor/icons.svg', 'assets/svg/editor-icons.svg')
  .disableNotifications()
  .js('resources/js/main.js', 'js/').vue()
  .js('resources/js/front-end/modules/FormBuilder.js', 'assets/js')
  .js('resources/js/front-end/plugins/FormValidate.js', 'assets/js')
  .sass('resources/sass/main.scss', 'css')
  .webpackConfig({
    resolve: {
      alias: {
        'jquery': path.resolve(path.join(__dirname, 'node_modules', 'jquery'))
      }
    },
    plugins: [
      new webpack.ProvidePlugin({
        $: 'jquery',
        jquery: 'jquery',
        'window.jQuery': 'jquery',
        jQuery: 'jquery'
      })
    ]
  })
;

if (mix.inProduction()) {
  mix.version();
}
