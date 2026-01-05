<script setup>
const emit = defineEmits(['edit']);
defineProps({
    tasks: Array
});

const TASK_STATUSES = {
    'todo': { label: 'Da fare', color: 'bg-yellow-500' },
    'in-progress': { label: 'In corso', color: 'bg-green-500' },
    'done': { label: 'Completata', color: 'bg-gray-500' },
    'blocked': { label: 'Bloccata', color: 'bg-red-500' }
};

const TASK_PRIORITIES = {
    'low': { label: 'Bassa', color: 'text-green-400' },
    'medium': { label: 'Media', color: 'text-yellow-400' },
    'high': { label: 'Alta', color: 'text-red-400' }
};

</script>

<template>
    <div class="bg-gray-900/50 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-widest">
                <th class="px-6 py-4 font-medium">Titolo</th>
                <th class="px-6 py-4 font-medium text-center">Stato</th>
                <th class="px-6 py-4 font-medium text-center">Priorità</th>
                <th class="px-6 py-4 font-medium">Scadenza</th>
                <th class="px-6 py-4 font-medium text-right">Progresso</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
            <tr v-for="task in tasks" :key="task.id"
                class="hover:bg-gray-800/30 transition-colors group cursor-pointer"
                @click="emit('edit', task)"
            >
                <td class="px-6 py-4">
                    <div class="text-white font-medium group-hover:text-[#07b4f6] transition-colors">
                        {{ task.title }}
                    </div>
                    <div class="text-[10px] uppercase tracking-tighter text-gray-500 font-bold">
                        {{ task.type }}
                    </div>
                </td>

                <td class="px-6 py-4 text-center">
                        <span v-if="TASK_STATUSES[task.status]"
                              :class="[
                                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border',
                                  TASK_STATUSES[task.status].color.replace('bg-', 'text-').replace('500', '400'),
                                  TASK_STATUSES[task.status].color.replace('bg-', 'bg-') + '/10',
                                  'border-' + TASK_STATUSES[task.status].color.split('-')[1] + '-500/20'
                              ]">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', TASK_STATUSES[task.status].color]"></span>
                            {{ TASK_STATUSES[task.status].label }}
                        </span>
                </td>

                <td class="px-6 py-4 text-center">
                        <span v-if="TASK_PRIORITIES[task.priority]"
                              :class="['text-[11px] font-bold uppercase flex items-center justify-center gap-1.5', TASK_PRIORITIES[task.priority].color]">
                            <i class="fas fa-flag text-[9px]"></i>
                            {{ TASK_PRIORITIES[task.priority].label }}
                        </span>
                </td>

                <td class="px-6 py-4 text-sm text-gray-400">
                    <div class="flex items-center">
                        <i class="far fa-calendar-alt mr-2 text-gray-600"></i>
                        {{ task.due_date ? new Date(task.due_date).toLocaleDateString('it-IT') : '-' }}
                    </div>
                </td>

                <td class="px-6 py-4 text-right">
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-xs font-bold text-[#07b4f6]">{{ task.progress }}%</span>
                        <div class="w-16 h-1 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-[#07b4f6] transition-all duration-500"
                                 :style="{ width: task.progress + '%' }"></div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr v-if="tasks.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                    <i class="fas fa-inbox block text-2xl mb-2 opacity-20"></i>
                    Nessuna attività trovata per questo progetto.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
