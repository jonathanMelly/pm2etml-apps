const mix = require('laravel-mix');

require('laravel-mix-copy-watched');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .js(['resources/js/app.js'], 'public/js')

    .postCss('resources/css/app.css', 'public/css', [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ])

    .sass('resources/sass/app.scss', 'public/css')

//custom js
    .copyWatched('resources/js/helper.js','public/js')

//dropzone
    //.copyWatched('node_modules/dropzone/dist/basic.css', 'public/css/')
    //.copyWatched('node_modules/dropzone/dist/basic.css.map', 'public/css/')

    .copyWatched('node_modules/dropzone/dist/dropzone.css.map', 'public/css/')
    .copyWatched('node_modules/dropzone/dist/dropzone.css', 'public/css/')

    .copyWatched('node_modules/dropzone/dist/dropzone-min.js', 'public/js/')
    .copyWatched('node_modules/dropzone/dist/dropzone-min.js.map', 'public/js/')

//echarts
    .copyWatched('node_modules/echarts/dist/*.min.js', 'public/js/',{ base: 'node_modules/echarts/dist' })

