import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as path from "path";
// import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import i18n from 'laravel-vue-i18n/vite';
import {tscWatch} from "vite-plugin-tsc-watch";
import {watch} from "vite-plugin-watch";

export default defineConfig({
    plugins: [
        laravel([
            //css
            'resources/css/app.css', //mainly tailwind
            'resources/sass/app.scss', //mainly fa

            //test

            //js
            'resources/js/app.js', //main laravel js
            'resources/js/helper.js', //custom helpers


            'resources/js/jobApplication.js',

            'resources/js/dropzone.js', //for draq/drop file upload
            'resources/js/dashboard-charts.js',
            'resources/js/contract-dispatch.js', //for contract evaluation bulk upload

            //inertia
            'resources/js/apps.ts',//

        ]),

        // react(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(),
        watch({
            pattern: "routes/**/*.php",
            command: "php artisan ziggy:generate --types-only",
        }),
        tscWatch()
    ],
    resolve: {
        alias: {
            '~fa': path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/scss'),
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),// avoid having ziggy in vendor+node_modules
        }
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    "echarts-core": ['echarts/core'],
                    "echarts-charts": ['echarts/charts'],
                }
            }
        },
    },
});
