<script setup>
import { computed } from 'vue';
import TaskRow from './TaskRow.vue';

const emit = defineEmits(['edit', 'toggle', 'expand-all', 'collapse-all']);
const props = defineProps({
    tasks: Array,
    expandedTasks: {
        type: Object,
        required: true
    }
});

/**
 * Builds a nested tree structure from a flat array of tasks
 */
const taskTree = computed(() => {
    const map = {};
    const tree = [];

    if (!props.tasks) return [];

    // Create a map of all tasks
    props.tasks.forEach(task => {
        map[task.id] = { ...task, children: [] };
    });

    // Assign children to their respective parents
    props.tasks.forEach(task => {
        if (task.task_parent_id && map[task.task_parent_id]) {
            map[task.task_parent_id].children.push(map[task.id]);
        } else if (!task.task_parent_id) {
            tree.push(map[task.id]);
        }
    });

    return tree;
});
</script>

<template>
    <div class="bg-gray-900/50 rounded-xl border border-gray-800 overflow-hidden">
        <div class="flex items-center justify-end gap-2 px-4 py-3 border-b border-gray-800 bg-gray-950/30">
            <button
                type="button"
                @click="emit('expand-all')"
                class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg bg-gray-800/60 border border-gray-700 text-gray-300 hover:text-white hover:border-[#07b4f6]/40 transition-all"
            >
                Espandi tutto
            </button>
            <button
                type="button"
                @click="emit('collapse-all')"
                class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg bg-gray-800/60 border border-gray-700 text-gray-300 hover:text-white hover:border-[#07b4f6]/40 transition-all"
            >
                Chiudi tutto
            </button>
        </div>
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-800/50 text-gray-400 text-[10px] uppercase tracking-widest border-b border-gray-800">
                <th class="px-6 py-4 font-medium w-24 text-center">Numero</th>
                <th class="px-6 py-4 font-medium">Titolo</th>
                <th class="px-6 py-4 font-medium text-center">Stato</th>
                <th class="px-6 py-4 font-medium">Assegnato a</th>
                <th class="px-6 py-4 font-medium">Priorità</th>
                <th class="px-6 py-4 font-medium">Scadenza</th>
                <th class="px-6 py-4 font-medium text-right">Progresso</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
            <template v-for="task in taskTree" :key="task.id">
                <TaskRow
                    :task="task"
                    :level="0"
                    :expanded-tasks="expandedTasks"
                    @toggle="(id) => emit('toggle', id)"
                    @edit="(t) => emit('edit', t)"
                />
            </template>

            <tr v-if="taskTree.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">
                    Nessuna attività trovata...
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
