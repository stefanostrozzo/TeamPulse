<template>
    <button
        type="button"
        :class="[
            'nav-item w-full flex items-center px-3 py-3 rounded-xl text-gray-300 hover:bg-[#07b4f6]/15 hover:text-[#07b4f6] transition',
            { 'bg-[#07b4f6]/20 text-[#07b4f6] font-medium': active },
        ]"
        @click="$emit('click')"
    >
        <div class="nav-icon w-6 h-6 mr-3 flex items-center justify-center flex-shrink-0">
            <slot name="icon" />
        </div>
        <div v-if="!collapsed" class="nav-text whitespace-nowrap overflow-hidden">
            <slot />
        </div>
        <span v-if="!collapsed && badge" class="badge ml-auto bg-[#07b4f6] text-white px-2 py-0.5 rounded-full text-xs font-semibold">
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