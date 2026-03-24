<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';
import draggable from 'vuedraggable';
import Swal from 'sweetalert2';
import Modal from '@/Components/Items/Modal.vue';
import Select from 'primevue/select';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    }
});

const emit = defineEmits(['editTask']);

const columns = ref([]);
const loading = ref(true);
const newColumnName = ref('');
const showAddColumn = ref(false);
const columnInput = ref(null);

// Modal state
const showTaskModal = ref(false);
const selectedTasks = ref([]);
const selectedColumnId = ref(null);
const availableTasksForModal = ref([]);
const submittingTasks = ref(false);
const taskSearchQuery = ref('');

const filteredAvailableTasks = computed(() => {
    if (!taskSearchQuery.value) return availableTasksForModal.value;
    const query = taskSearchQuery.value.toLowerCase();
    return availableTasksForModal.value.filter(t => t.title.toLowerCase().includes(query));
});

const fetchColumns = async () => {
    try {
        const response = await axios.get(route('kanban.columns.index', props.project.id));
        columns.value = response.data.columns;
    } catch (e) {
        console.error('Failed to fetch kanban columns', e);
    } finally {
        loading.value = false;
    }
};

const addColumn = async () => {
    if (!newColumnName.value.trim()) return;
    try {
        const response = await axios.post(route('kanban.columns.store', props.project.id), {
            name: newColumnName.value.trim()
        });
        columns.value.push({ ...response.data.column, kanban_tasks: [] });
        newColumnName.value = '';
        showAddColumn.value = false;
    } catch (e) {
        console.error('Failed to add column', e);
    }
};

const openAddColumn = async () => {
    showAddColumn.value = true;
    await nextTick();
    columnInput.value?.focus();
};

const renameColumn = (column) => {
    Swal.fire({
        title: 'Rinomina colonna',
        input: 'text',
        inputValue: column.name,
        showCancelButton: true,
        confirmButtonText: 'Salva',
        cancelButtonText: 'Annulla',
        background: '#1f2937',
        color: '#ffffff',
    }).then(async (result) => {
        if (result.isConfirmed && result.value.trim()) {
            try {
                await axios.put(route('kanban.columns.update', { project: props.project.id, column: column.id }), {
                    name: result.value.trim()
                });
                column.name = result.value.trim();
            } catch (e) {
                console.error(e);
            }
        }
    });
};

const deleteColumn = (column, index) => {
    Swal.fire({
        title: 'Sei sicuro?',
        text: 'La colonna verrà eliminata e i task verranno rimossi dalla tua board (ma non dal progetto).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sì, elimina',
        cancelButtonText: 'Annulla',
        background: '#1f2937',
        color: '#ffffff',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(route('kanban.columns.destroy', { project: props.project.id, column: column.id }));
                columns.value.splice(index, 1);
            } catch (e) {
                console.error(e);
            }
        }
    });
};

const onColumnEnd = async () => {
    // Reorder columns
    columns.value.forEach((col, index) => {
        col.order = index;
    });

    try {
        await axios.put(route('kanban.columns.reorder', props.project.id), {
            columns: columns.value.map(c => ({ id: c.id, order: c.order }))
        });
    } catch (e) {
        console.error(e);
    }
};

const onTaskEnd = async () => {
    // Collect the new tasks order
    const payloadColumns = columns.value.map(col => ({
        id: col.id,
        tasks: col.kanban_tasks.map(kt => kt.id)
    }));

    try {
        await axios.put(route('kanban.tasks.reorder', props.project.id), {
            columns: payloadColumns
        });
    } catch (e) {
        console.error(e);
        // Silently fail or show toast
    }
};

const openTaskModal = () => {
    const existingTaskIds = new Set();
    columns.value.forEach(col => {
        if (col.kanban_tasks) {
            col.kanban_tasks.forEach(kt => existingTaskIds.add(kt.task_id));
        }
    });

    availableTasksForModal.value = props.project.tasks.filter(t => !existingTaskIds.has(t.id));

    if (availableTasksForModal.value.length === 0) {
        Swal.fire({
            title: 'Nessun task disponibile',
            text: 'Tutti i task del progetto sono già nella tua board.',
            icon: 'info',
            background: '#1f2937',
            color: '#ffffff',
        });
        return;
    }

    if (columns.value.length === 0) {
        Swal.fire({
            title: 'Nessuna colonna',
            text: 'Devi creare almeno una colonna prima di poter aggiungere task.',
            icon: 'warning',
            background: '#1f2937',
            color: '#ffffff',
        });
        return;
    }

    selectedTasks.value = [];
    taskSearchQuery.value = '';
    selectedColumnId.value = columns.value[0].id;
    showTaskModal.value = true;
};

const submitTasks = async () => {
    if (selectedTasks.value.length === 0) return;
    
    submittingTasks.value = true;
    try {
        const response = await axios.post(route('kanban.tasks.store', props.project.id), {
            kanban_column_id: selectedColumnId.value,
            task_ids: selectedTasks.value
        });
        
        const targetColumn = columns.value.find(col => col.id === selectedColumnId.value);
        if (targetColumn) {
            targetColumn.kanban_tasks.push(...response.data.kanbanTasks);
        }
        
        showTaskModal.value = false;
    } catch (e) {
        console.error(e);
        Swal.fire({
            title: 'Errore',
            text: 'Si è verificato un errore durante l\'aggiunta dei task.',
            icon: 'error',
            background: '#1f2937',
            color: '#ffffff',
        });
    } finally {
        submittingTasks.value = false;
    }
};

const removeTask = (column, ktIndex, kanbanTask) => {
    try {
        axios.delete(route('kanban.tasks.destroy', { project: props.project.id, kanbanTask: kanbanTask.id }));
        column.kanban_tasks.splice(ktIndex, 1);
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    fetchColumns();
});
</script>

<template>
    <div class="flex flex-col h-[calc(100vh-250px)]">
        <!-- Toolbar Fixed Header -->
        <div class="flex justify-between items-center mb-6 pl-2">
            <button
                v-if="columns.length > 0"
                @click="openTaskModal"
                class="px-5 py-2.5 border border-transparent bg-[#07b4f6] text-white hover:bg-[#06a3dd] rounded-xl flex items-center justify-center transition-colors font-bold shadow-lg shadow-[#07b4f6]/20 active:scale-95"
            >
                <i class="fas fa-plus mr-2"></i> Aggiungi Task
            </button>
            <div></div> <!-- Right-side placeholder if needed -->
        </div>

        <div class="flex-1 overflow-x-auto pb-4 custom-scrollbar">
            <div v-if="loading" class="flex justify-center items-center h-full text-gray-400">
                <i class="fas fa-spinner fa-spin text-3xl"></i>
            </div>

            <div v-else class="flex items-start space-x-6 h-full p-2">
            <!-- Columns List -->
            <draggable
                v-model="columns"
                group="columns"
                item-key="id"
                handle=".column-handle"
                class="flex items-start space-x-6 h-full"
                @end="onColumnEnd"
            >
                <template #item="{ element: column, index: colIndex }">
                    <div class="flex-shrink-0 w-80 bg-gray-800 rounded-xl overflow-hidden shadow-lg border border-gray-700 flex flex-col max-h-full">
                        <!-- Column Header -->
                        <div class="p-4 border-b border-gray-700 flex justify-between items-center group bg-gray-800/80 column-handle cursor-move">
                            <h3 class="font-bold text-gray-200 truncate pr-2">{{ column.name }}</h3>
                            <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="renameColumn(column)" class="text-gray-400 hover:text-white transition-colors" title="Rinomina">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button @click.stop="deleteColumn(column, colIndex)" class="text-gray-400 hover:text-red-400 transition-colors" title="Elimina">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Column Body (Tasks) -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                            <draggable
                                v-model="column.kanban_tasks"
                                group="tasks"
                                item-key="id"
                                class="min-h-[50px]"
                                ghost-class="ghost-card"
                                drag-class="drag-card"
                                @end="onTaskEnd"
                            >
                                <template #item="{ element: kt, index: ktIndex }">
                                    <div @dblclick="emit('editTask', kt.task)" class="bg-gray-900 p-3 rounded-lg border border-gray-700 shadow-sm cursor-pointer hover:border-gray-500 transition-colors group relative">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-sm font-medium text-gray-200 line-clamp-2">{{ kt.task.title }}</span>
                                            <button @click.stop="removeTask(column, ktIndex, kt)" class="text-gray-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity ml-2">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-tag"></i>
                                                <span class="capitalize">{{ kt.task.status }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <div v-if="kt.task.assignee" class="w-6 h-6 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-bold" :title="kt.task.assignee.name">
                                                    {{ kt.task.assignee.name.charAt(0) }}
                                                </div>
                                                <div v-else class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center">
                                                    <i class="fas fa-user text-[10px]"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </draggable>
                        </div>

                        <!-- Column Footer (Removed old add task button) -->
                        <div class="p-2 border-t border-gray-700 bg-gray-800/50 min-h-2">
                        </div>
                    </div>
                </template>
            </draggable>

            <!-- Add Column Button/Form -->
            <div class="flex-shrink-0 w-80">
                <div v-if="!showAddColumn" class="space-y-4">
                    <button
                        @click="openAddColumn"
                        class="w-full py-4 border-2 border-dashed border-gray-700 rounded-xl flex items-center justify-center text-gray-400 hover:text-white hover:border-gray-500 transition-colors bg-gray-800/30 font-medium"
                    >
                        <i class="fas fa-plus mr-2"></i> Nuova Colonna
                    </button>
                </div>
                <div v-else class="bg-gray-800 rounded-xl p-4 border border-gray-700 shadow-lg">
                    <input
                        ref="columnInput"
                        v-model="newColumnName"
                        @keyup.enter="addColumn"
                        type="text"
                        placeholder="Nome colonna..."
                        class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] mb-3"
                        autofocus
                    />
                    <div class="flex space-x-2">
                        <button @click="addColumn" class="flex-1 bg-[#07b4f6] hover:bg-[#06a3de] text-white py-1.5 rounded-lg text-sm font-medium transition-colors">Aggiungi</button>
                        <button @click="showAddColumn = false; newColumnName = ''" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-1.5 rounded-lg text-sm font-medium transition-colors">Annulla</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Task Modal -->
        <Modal :show="showTaskModal" @close="showTaskModal = false" maxWidth="lg">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white tracking-tight">Aggiungi Task alla Board</h2>
                    <p class="text-sm text-gray-400 mt-1">Seleziona i task da aggiungere e la colonna di destinazione.</p>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">Colonna di destinazione</label>
                        <Select
                            v-model="selectedColumnId"
                            :options="columns"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            placeholder="Cerca una colonna..."
                            appendTo="self"
                            class="w-full"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-3">
                            Seleziona i Task <span class="text-xs text-gray-500 font-normal">({{ selectedTasks.length }} selezionati)</span>
                        </label>
                        
                        <!-- Search Bar -->
                        <div class="relative mb-3">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                            <input 
                                type="text" 
                                v-model="taskSearchQuery" 
                                placeholder="Cerca task per nome..." 
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-[#07b4f6] placeholder-gray-500"
                            >
                        </div>

                        <div class="max-h-60 overflow-y-auto space-y-2 custom-scrollbar pr-2 border border-gray-800 rounded-lg p-2 bg-gray-800/30">
                            <div v-if="filteredAvailableTasks.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                Nessun task trovato con questa ricerca.
                            </div>
                            <label 
                                v-for="task in filteredAvailableTasks" 
                                :key="task.id"
                                class="flex items-start space-x-3 p-3 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer hover:bg-gray-700/50 transition-colors"
                                :class="{'border-[#07b4f6] bg-[#07b4f6]/5': selectedTasks.includes(task.id)}"
                            >
                                <div class="flex-shrink-0 mt-0.5">
                                    <input 
                                        type="checkbox" 
                                        :value="task.id" 
                                        v-model="selectedTasks"
                                        class="w-4 h-4 rounded text-[#07b4f6] focus:ring-[#07b4f6] bg-gray-900 border-gray-600"
                                    >
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-200 line-clamp-2">{{ task.title }}</p>
                                    <p class="text-xs text-gray-400 capitalize mt-1">{{ task.priority }} priority</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-4 border-t border-gray-800">
                    <button
                        @click="showTaskModal = false"
                        class="px-6 py-2.5 text-sm font-semibold text-gray-300 hover:text-white rounded-xl transition-all"
                    >
                        Annulla
                    </button>
                    <button
                        @click="submitTasks"
                        :disabled="submittingTasks || selectedTasks.length === 0"
                        class="flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white bg-[#07b4f6] hover:bg-[#06a3dd] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-[#07b4f6]/20"
                    >
                        <i v-if="submittingTasks" class="fas fa-spinner fa-spin"></i>
                        Salva
                    </button>
                </div>
            </div>
        </Modal>
        </div>
    </div>
</template>

<style scoped>
.ghost-card {
    opacity: 0.5;
    background: #374151 !important;
    border: 1px dashed #6b7280 !important;
}

.drag-card {
    cursor: grabbing !important;
    transform: rotate(2deg);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #6b7280;
}
</style>
