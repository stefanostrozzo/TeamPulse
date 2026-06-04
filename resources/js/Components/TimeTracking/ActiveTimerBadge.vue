<script setup>
import { useTimeTrackingStore } from '@/stores/timeTrackingStore';
import { storeToRefs } from 'pinia';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['navigate-to-time', 'open-task']);

const store = useTimeTrackingStore();
const { hasActiveTimer, activeTimer, elapsedFormatted } = storeToRefs(store);

const handleOpenTask = () => {
    if (activeTimer.value?.task) {
        emit('open-task', {
            id: activeTimer.value.task.id,
            projectId: activeTimer.value.task.project_id
        });
    }
};
</script>

<template>
    <div
        v-if="hasActiveTimer"
        @click="handleOpenTask"
        :class="[
            'flex items-center gap-2 px-3 py-2 rounded-xl cursor-pointer transition',
            'bg-[#07b4f6]/10 border border-[#07b4f6]/30 hover:bg-[#07b4f6]/20',
            collapsed ? 'justify-center' : '',
        ]"
        :title="collapsed ? `Timer: ${elapsedFormatted} â€” #${activeTimer?.task?.id} ${activeTimer?.task?.title}` : ''"
    >
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#07b4f6] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#07b4f6]"></span>
        </span>
        <template v-if="!collapsed">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-mono text-[#07b4f6]">{{ elapsedFormatted }}</p>
                <p class="text-[10px] text-gray-400 truncate">#{{ activeTimer?.task?.id }} - {{ activeTimer?.task?.title }}</p>
            </div>
        </template>
    </div>
</template>
