<template>
    <div class="relative">
        <div class="logo-container flex items-center gap-3 px-4 py-4 border-b border-gray-700" :class="{ 'pr-10': !collapsed }">
            <div class="logo-icon w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0 bg-gradient-to-br ">
                <slot name="icon">
                    <i class="fas fa-project-diagram text-white"></i>
                </slot>
            </div>
            <div v-if="!collapsed" class="logo-text font-bold text-base whitespace-nowrap overflow-hidden text-white">
                <slot>Team<span class="text-[#07b4f6]">Pulse</span></slot>
            </div>
        </div>

        <button
            class="sidebar-toggle absolute top-1/2 -translate-y-1/2 -right-4 w-8 h-8 rounded-full bg-gray-700 border-2 border-gray-800 flex items-center shadow-lg text-gray-200 hover:bg-gray-600 transition-colors z-10"
            type="button"
            @click="$emit('toggle')"
        >
            <i class="fas text-sm" :class="collapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({ collapsed: { type: Boolean, default: false } })
defineEmits(['toggle'])
</script>

<style scoped>
.logo-container { 
    position: relative; 
    transition: padding-right 0.2s ease;
}
</style>