<script setup>
const props = defineProps({
    task: Object,
    level: Number,
    expandedTasks: Object
});

const emit = defineEmits(['toggle', 'edit']);

/** * Styling mapping for task statuses
 */
const TASK_STATUSES = {
    'todo': { label: 'Da fare', color: 'text-yellow-500 bg-yellow-500/10 border-yellow-500/20' },
    'in-progress': { label: 'In corso', color: 'text-[#07b4f6] bg-[#07b4f6]/10 border-[#07b4f6]/20' },
    'done': { label: 'Completata', color: 'text-green-500 bg-green-500/10 border-green-500/20' },
    'blocked': { label: 'Bloccata', color: 'text-red-500 bg-red-500/10 border-red-500/20' }
};

const PRIORITY_LABELS = {
    'low': { label: 'Bassa', color: 'text-gray-400' },
    'medium': { label: 'Media', color: 'text-yellow-400' },
    'high': { label: 'Alta', color: 'text-red-400' }
};
</script>

<template>
    <tr
        @click="emit('edit', task)"
        class="border-b border-gray-800/50 hover:bg-gray-800/40 transition-all cursor-pointer group"
    >
        <td class="px-6 py-4 text-[10px] text-[#07b4f6] font-mono font-bold text-center">
            #{{ task.id }}
        </td>

        <td class="px-6 py-4" :style="{ paddingLeft: (level * 28 + 24) + 'px' }">
            <div class="flex items-center gap-3">
                <button
                    v-if="task.children && task.children.length > 0"
                    @click.stop="emit('toggle', task.id)"
                    class="w-5 h-5 flex items-center justify-center rounded-md bg-gray-800 border border-gray-700 text-gray-500 hover:text-white transition-all"
                >
                    <i class="fas fa-chevron-right text-[9px] transition-transform duration-200"
                       :class="{ 'rotate-90': expandedTasks.has(task.id) }"></i>
                </button>
                <div v-else class="w-5"></div>

                <span class="text-sm text-gray-200 font-medium group-hover:text-[#07b4f6] transition-colors truncate">
                    {{ task.title }}
                </span>
            </div>
        </td>

        <td class="px-6 py-4 text-center">
            <span v-if="task.status"
                  class="px-2 py-0.5 rounded text-[9px] font-black uppercase border"
                  :class="TASK_STATUSES[task.status]?.color">
                {{ TASK_STATUSES[task.status]?.label || task.status }}
            </span>
        </td>

        <td class="px-6 py-4 text-xs text-gray-400">
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center text-[8px] font-bold text-white uppercase">
                    {{ task.assignee ? task.assignee.name.substring(0,2) : '?' }}
                </div>
                <span>{{ task.assignee?.name || 'Non assegnato' }}</span>
            </div>
        </td>

        <td class="px-6 py-4 text-[10px] font-bold uppercase" :class="PRIORITY_LABELS[task.priority]?.color">
            {{ PRIORITY_LABELS[task.priority]?.label || '-' }}
        </td>

        <td class="px-6 py-4 text-xs text-gray-400 italic">
            {{ task.due_date ? new Date(task.due_date).toLocaleDateString('it-IT') : '-' }}
        </td>

        <td class="px-6 py-4 text-right">
            <div class="flex flex-col items-end gap-1">
                <span class="text-[10px] font-bold text-[#07b4f6]">{{ task.progress }}%</span>
                <div class="w-16 h-1 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#07b4f6] to-cyan-400"
                         :style="{ width: task.progress + '%' }"></div>
                </div>
            </div>
        </td>
    </tr>

    <template v-if="expandedTasks.has(task.id)">
        <TaskRow
            v-for="child in task.children"
            :key="child.id"
            :task="child"
            :level="level + 1"
            :expanded-tasks="expandedTasks"
            @toggle="(id) => emit('toggle', id)"
            @edit="(t) => emit('edit', t)"
        />
    </template>
</template>
