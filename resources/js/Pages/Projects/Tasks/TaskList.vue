<script setup>
import { computed, ref } from 'vue';
import TaskRow from './TaskRow.vue';

const emit = defineEmits(['edit']);
const props = defineProps({
    tasks: Array
});

// Set to track expanded task IDs for the tree view
const expandedTasks = ref(new Set());

/**
 * Toggles a task's expanded state to show/hide subtasks
 */
const toggleExpand = (taskId) => {
    if (expandedTasks.value.has(taskId)) {
        expandedTasks.value.delete(taskId);
    } else {
        expandedTasks.value.add(taskId);
    }
};

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
                    @toggle="toggleExpand"
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
