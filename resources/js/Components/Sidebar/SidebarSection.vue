<template>
    <div class="nav-section mb-6">
        <div v-if="title && !collapsed" class="nav-title text-xs font-semibold uppercase text-gray-400 tracking-wider px-3 mb-3">
            {{ title }}
        </div>
        <div>
            <slot />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
    collapsed: { type: Boolean, default: false },
})
</script>


