<script setup>
const props = defineProps({ project: Object });
const emit = defineEmits(['open-project']);
</script>

<template>
    <div
        @click="emit('open-project', project.id)"
        class="group bg-gray-900 rounded-2xl border border-gray-800 p-5 hover:border-[#07b4f6]/40 transition-all cursor-pointer flex flex-col h-full"
    >
        <div class="flex justify-between items-start mb-4">
            <h4 class="font-bold text-white group-hover:text-[#07b4f6] transition-colors truncate mr-2">
                {{ project.name }}
            </h4>
            <span class="shrink-0 text-[10px] font-bold px-2 py-1 bg-gray-800 text-gray-400 rounded-lg uppercase tracking-wider">
                {{ project.status }}
            </span>
        </div>

        <div class="mt-auto">
            <p class="text-[11px] text-gray-500 uppercase font-bold tracking-widest mb-3">Le mie attività</p>
            <div v-if="project.tasks?.length" class="flex flex-wrap gap-2 max-h-20 overflow-hidden">
                <div
                    v-for="task in project.tasks"
                    :key="task.id"
                    class="flex items-center gap-2 px-3 py-1 bg-gray-800/50 border border-gray-700 rounded-full max-w-37.5"
                >
                    <div :class="[
                        'w-2.5 h-2.5 rounded-full shrink-0',
                        task.priority === 'high' ? 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.4)]' : 'bg-blue-500'
                    ]"></div>
                    <span class="text-xs text-gray-300 truncate font-medium">{{ task.name }}</span>
                </div>
            </div>
            <p v-else class="text-sm text-gray-600 italic">Nessun task assegnato</p>
        </div>
    </div>
</template>
