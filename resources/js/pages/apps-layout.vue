<script setup>
defineProps()

import '../../css/app.css'
import '../../sass/app.scss'
</script>

<template>
    <div class="min-h-screen bg-base-100 flex flex-col">
        <slot />
    </div>
</template>
