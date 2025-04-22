<script setup>
import {usePage} from "@inertiajs/inertia-vue3";

defineProps()

import '../../css/app.css'
import '../../sass/app.scss'


const page = usePage()
</script>

<template>
    <!DOCTYPE html>
    <html lang="{{ page.props['lang']  }}" data-theme="{{ page.props['theme'] }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    </head>
    <body class="font-sans antialiased">

    <div class="min-h-screen bg-base-100 flex flex-col">

        <main class="min-h-[calc(100vh-2.5rem)]">
            <div class="min-h-screen bg-base-100 flex flex-col">
                hello
                <slot />
            </div>
        </main>

        <div class="sm:mb-2 mb-1 sm:mt-3 mt-2 h-5">
            <footer class="sm:mx-6 bg-base-300 text-base-content rounded-box text-xs">
                <div class="flex flex-row justify-center content-center gap-1">
                    <div>
                        Copyright © {{ new Date().getFullYear()}} - All right reserved by PM2ETML -
                    </div>
                    <div>
                        Version {{ page.props["version"] }}
                    </div>



                    <div v-if="page.props['environment']==='production' || page.props['environment']==='staging'">

                    <a target="_blank" href="https://github.com/jonathanMelly/pm2etml-apps#readme"><i class="fa-brands fa-github"></i> </a>

                    <a href="https://github.com/jonathanMelly/pm2etml-apps/issues" target="_blank">
                        <img class="h-4"
                             src="https://img.shields.io/github/issues-raw/jonathanMelly/pm2etml-apps?style=plastic?cacheSeconds=54000">
                    </a>

                    <a href="https://wakatime.com/@bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/projects/ctusfaxkkd" target="_blank">
                        <img class="h-4"
                             src="https://wakatime.com/badge/user/bf7fcc14-d7d0-41c4-99cb-bbe8ecef41bf/project/4fb00346-5e05-4e6b-a906-57e91c256d09.svg"
                             alt="wakatime">
                    </a>

                    </div>

                </div>
            </footer>
        </div>
    </div>

    </body>
    </html>

</template>
