import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import * as esbuild from 'esbuild';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';
import path from 'node:path';

const root = path.dirname(fileURLToPath(import.meta.url));
const resolve = (p) => path.resolve(root, p);

// replaces Laravel Mix's copyDirectory/copy — deterministically mirrors the
// static img + editor-icons sprite into the assets output after each build, and
// emits the standalone (non-module) FormValidate.js bundle.
function copyStaticAssets() {
  return {
    name: 'refined-copy-static-assets',
    apply: 'build',
    async closeBundle() {
      fs.cpSync(resolve('resources/img'), resolve('assets/img'), { recursive: true });
      fs.mkdirSync(resolve('assets/svg'), { recursive: true });
      fs.copyFileSync(
        resolve('resources/svg/editor/icons.svg'),
        resolve('assets/svg/editor-icons.svg')
      );

      // FormValidate is loaded as a plain <script> too, so ship it as a
      // self-contained IIFE that exposes window.FormValidate.
      await esbuild.build({
        entryPoints: [resolve('resources/js/front-end/plugins/FormValidate.js')],
        outfile: resolve('assets/js/FormValidate.js'),
        bundle: true,
        minify: true,
        format: 'iife',
        globalName: '__refinedFormValidate',
        footer: { js: 'window.FormValidate=__refinedFormValidate.FormValidate;' },
      });
    },
  };
}

// this package ships pre-built assets that are published to the consumer's
// public/vendor/refined/core directory. the admin blade loads them with plain
// <script>/<link> tags (not @vite), so the output filenames must stay fixed.
export default defineConfig({
  resolve: {
    alias: {
      jquery: resolve('node_modules/jquery'),
    },
  },

  css: {
    preprocessorOptions: {
      scss: {
        // node_modules on the load path lets bare imports (e.g. vanilla-picker) resolve
        loadPaths: ['node_modules'],
        // the vanilla-picker vendor css is still pulled in via a plain @import
        silenceDeprecations: ['import', 'legacy-js-api'],
      },
    },
  },

  build: {
    outDir: 'assets',
    emptyOutDir: false, // img/svg are copied here separately and must survive
    manifest: false,
    cssCodeSplit: false, // single main.css to match the existing publish layout
    // the admin bundle is loaded as one plain <script>, so it must stay a single
    // self-contained file — don't warn about its (expected) size.
    chunkSizeWarningLimit: 2000,
    rollupOptions: {
      input: {
        main: resolve('resources/js/main.js'),
        // FormBuilder is loaded as a plain (non-module) <script>, so it must be
        // fully self-contained — FormValidate is inlined into it, not split out.
        FormBuilder: resolve('resources/js/front-end/modules/FormBuilder.js'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/chunks/[name].js',
        // keep every entry self-contained: no shared chunks that a plain
        // <script> tag could not resolve.
        manualChunks: undefined,
        inlineDynamicImports: false,
        assetFileNames: (assetInfo) => {
          const name = assetInfo.names?.[0] ?? assetInfo.name ?? '';
          if (name.endsWith('.css')) {
            return 'css/main.css';
          }
          return 'assets/[name][extname]';
        },
      },
    },
  },

  plugins: [
    // template image/src paths point at the published /vendor/refined/core
    // runtime location, not bundled assets — don't let vue rewrite them.
    vue({
      template: {
        transformAssetUrls: {
          includeAbsolute: false,
        },
      },
    }),
    copyStaticAssets(),
  ],
});
