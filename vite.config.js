import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import * as path from "path";
// import react from '@vitejs/plugin-react';
// import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel([
            //css
            'resources/css/app.css', //mainly tailwind
            'resources/sass/app.scss', //mainly fa

            //test

            //js
            'resources/js/app.js', //main laravel js
            //'resources/js/fontawesome.js', doesn’t work with @click alpine directive
            'resources/js/helper.js', //custom helpers

            //node modules for prod
            'resources/js/dropzone.js', //for draq/drop file upload
            'resources/js/echarts.js', //for charts

        ]),

        // react(),
        // vue({
        //     template: {
        //         transformAssetUrls: {
        //             base: null,
        //             includeAbsolute: false,
        //         },
        //     },
        // }),
    ],
    resolve: {
        alias: {
            '~fa': path.resolve(__dirname, 'node_modules/@fortawesome/fontawesome-free/scss'),
        }
    },
});
