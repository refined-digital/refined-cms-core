import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import * as glob from 'glob';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const cssInputs = Object.fromEntries(
    glob
        .sync('resources/css/components/**/*.css')
        .filter((file) => !file.includes('_'))
        .map((file) => {
            return [
                path.relative('', file),
                fileURLToPath(new URL(file, import.meta.url)),
            ];
        }),
);

const jsInputs = Object.fromEntries(
    glob
        .sync('resources/js/components/**/*.js')
        .filter((file) => !file.includes('_'))
        .map((file) => {
            return [
                path.relative('', file),
                fileURLToPath(new URL(file, import.meta.url)),
            ];
        }),
);

const inputs = {
    'resources/css/main.css': 'resources/css/main.css',
    'resources/js/main.js': 'resources/js/main.js',
    ...cssInputs,
    ...jsInputs,
};


export default defineConfig({
    plugins: [
        laravel({
            input: inputs,
            refresh: ['resources/views/**', 'app/RefinedCMS/**'],
            publicDirectory: 'public',
            buildDirectory: 'build'
        }),
    ]
});
