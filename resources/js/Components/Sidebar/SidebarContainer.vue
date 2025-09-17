<template>
    <aside :class="['sidebar bg-gray-800 border-r border-gray-700 flex flex-col transition-all duration-300 relative z-40 overflow-x-hidden', { 'w-20': collapsed, 'w-72': !collapsed }]">
        <slot />
    </aside>
</template>

<script setup>
defineProps({
    collapsed: { type: Boolean, default: false },
})
</script>

<style scoped>
.sidebar { min-height: 100vh; }
</style>


