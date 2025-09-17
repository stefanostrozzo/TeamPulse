<template>
    <button
        type="button"
        :class="[
            'nav-item w-full flex items-center px-3 py-3 rounded-xl text-gray-300 hover:bg-violet-500/15 hover:text-violet-300 transition',
            { 'bg-violet-500/20 text-violet-400 font-medium': active },
        ]"
        @click="$emit('click')"
    >
        <div class="nav-icon w-6 h-6 mr-3 flex items-center justify-center flex-shrink-0">
            <slot name="icon" />
        </div>
        <div v-if="!collapsed" class="nav-text whitespace-nowrap overflow-hidden">
            <slot />
        </div>
        <span v-if="!collapsed && badge" class="badge ml-auto bg-violet-600 text-white px-2 py-0.5 rounded-full text-xs font-semibold">
            {{ badge }}
        </span>
        <div v-if="$slots.right" class="ml-auto flex-shrink-0">
            <slot name="right" />
        </div>
    </button>
    
    
</template>

<script setup>
defineProps({
    active: { type: Boolean, default: false },
    badge: { type: [String, Number], default: '' },
    collapsed: { type: Boolean, default: false },
})
defineEmits(['click'])
</script>


