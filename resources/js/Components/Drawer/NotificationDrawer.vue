<template>
    <div>
        <div
            class="notification-drawer fixed top-0 right-0 h-screen bg-gray-800 border-l border-gray-700 z-50 transition-all duration-300 flex flex-col"
            :class="open ? 'w-96 translate-x-0' : 'w-96 translate-x-full'"
        >
            <div class="drawer-header p-6 border-b border-gray-700 flex items-center justify-between">
                <h3 class="drawer-title text-lg font-semibold">Notifiche</h3>
                <button class="drawer-close text-gray-200" type="button" @click="$emit('close')">×</button>
            </div>
            <div class="drawer-content flex-1 overflow-y-auto p-4 space-y-3">
                <slot>
                    <div class="notification-item p-4 rounded-xl bg-gray-700/70">
                        <div class="notification-title font-medium mb-1">Nessuna notifica</div>
                        <div class="notification-time text-xs text-gray-400">—</div>
                    </div>
                </slot>
            </div>
            <div class="drawer-footer p-4 border-t border-gray-700 text-center">
                <button class="mark-all-read text-[#07b4f6]" type="button" @click="$emit('mark-all-read')">Segna tutte come lette</button>
            </div>
        </div>
        <div
            class="overlay fixed inset-0 bg-black/50 z-40"
            v-if="open"
            @click="$emit('close')"
        />
    </div>
</template>

<script setup>
defineProps({
    open: { type: Boolean, default: false },
})
defineEmits(['close', 'mark-all-read'])
</script>


