<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch, computed } from 'vue';
import InputError from '@/Components/Items/InputError.vue';
import Select from 'primevue/select';

/**
 * Component Props
 * @property {Object} project - The parent project context containing members and tasks.
 * @property {Object|null} task - The task object if editing, null if creating.
 */
const props = defineProps({
    project: { type: Object, required: true },
    task: { type: Object, default: null }
});

const emit = defineEmits(['close', 'confirmDelete']);

/**
 * Inertia form helper initialized with reactive task attributes.
 */
const form = useForm({
    project_id: props.project.id,
    team_id: props.project.team_id,
    title: '',
    description: '',
    status: 'todo',
    priority: 'low',
    type: 'feature',
    assignee_id: null,
    start_date: '',
    due_date: '',
    progress: 0,
    task_parent_id: props.task?.task_parent_id ?? null,
});

// Options for static dropdowns (translated to Italian)
const statusOptions = [
    { label: 'Da Fare', value: 'todo' },
    { label: 'In Corso', value: 'in-progress' },
    { label: 'Completato', value: 'done' },
    { label: 'Bloccato', value: 'blocked' }
];

const priorityOptions = [
    { label: 'Bassa', value: 'low' },
    { label: 'Media', value: 'medium' },
    { label: 'Alta', value: 'high' }
];

const typeOptions = [
    { label: 'Funzionalità', value: 'feature' },
    { label: 'Bug/Errore', value: 'bug' },
    { label: 'Miglioramento', value: 'improvement' }
];

/**
 * Formats project members for the searchable assignee dropdown.
 */
const assigneeOptions = computed(() => {
    const members = props.project.members.map(m => ({
        label: m.name,
        value: m.id
    }));
    return [{ label: 'Non assegnato', value: null }, ...members];
});

/**
 * Circular reference prevention: recursively finds all children IDs.
 */
const getDescendantIds = (taskId, allTasks) => {
    let descendants = [];
    const children = allTasks.filter(t => t.task_parent_id === taskId);
    children.forEach(child => {
        descendants.push(child.id);
        descendants = [...descendants, ...getDescendantIds(child.id, allTasks)];
    });
    return descendants;
};

/**
 * Filters the project tasks to exclude the current task and its descendants
 * from being selected as a parent (prevents infinite loops).
 */
const availableParentOptions = computed(() => {
    let tasks = props.project.tasks || [];
    if (props.task) {
        const illegalIds = [props.task.id, ...getDescendantIds(props.task.id, tasks)];
        tasks = tasks.filter(t => !illegalIds.includes(t.id));
    }

    const options = tasks.map(t => ({
        label: `#${t.id} - ${t.title}`,
        value: t.id
    }));

    return [{ label: 'Nessuna (Attività principale)', value: null }, ...options];
});

/**
 * Hydrates the form fields when the component mounts or the task prop changes.
 */
const fillForm = () => {
    if (props.task) {
        form.title = props.task.title ?? '';
        form.description = props.task.description ?? '';
        form.status = props.task.status ?? 'todo';
        form.priority = props.task.priority ?? 'low';
        form.type = props.task.type ?? 'feature';
        form.assignee_id = props.task.assignee_id ?? null;
        form.progress = props.task.progress ?? 0;
        form.task_parent_id = props.task.task_parent_id ?? null;
        form.start_date = props.task.start_date ? props.task.start_date.substring(0, 10) : '';
        form.due_date = props.task.due_date ? props.task.due_date.substring(0, 10) : '';
    } else {
        form.reset();
        form.project_id = props.project.id;
        form.team_id = props.project.team_id;
    }
};

onMounted(fillForm);
watch(() => props.task, fillForm, { immediate: true, deep: true });

/**
 * Handles form submission for both Create and Update actions.
 */
const submit = () => {
    const options = {
        onSuccess: () => emit('close'),
        preserveScroll: true,
    };
    if (props.task?.id) {
        form.put(route('tasks.update', props.task.id), options);
    } else {
        form.post(route('tasks.store'), options);
    }
};
</script>

<template>
    <div class="flex flex-col h-full bg-gray-900 shadow-2xl">
        <div class="sticky top-0 z-10 bg-gray-900/80 backdrop-blur-md p-6 border-b border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-tasks text-[#07b4f6]"></i>
                <span class="text-xs font-bold uppercase tracking-widest text-gray-500 italic">Dettaglio Attività</span>
            </div>
            <div class="flex items-center space-x-2">
                <button v-if="task && $page.props.auth.user.permissions.includes('delete tasks')"
                        @click="$emit('confirmDelete', task)" class="p-2 text-gray-500 hover:text-red-500 transition-colors">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button @click="$emit('close')" class="p-2 text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex-grow p-8 space-y-8 pb-32 overflow-y-auto custom-scrollbar">
            <div>
                <textarea v-model="form.title" rows="1"
                          class="w-full bg-transparent border-none text-3xl font-bold text-white placeholder-gray-700 focus:ring-0 resize-none p-0 overflow-hidden"
                          placeholder="Titolo attività..."></textarea>
                <InputError :message="form.errors.title" />
            </div>

            <div class="mb-6 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">Attività Superiore</label>
                <Select v-model="form.task_parent_id" :options="availableParentOptions"
                        optionLabel="label" optionValue="value" filter
                        placeholder="Cerca attività genitore..." class="w-full custom-prime-select" />
                <p class="text-[10px] text-gray-600 ml-1 italic">Collega questa attività a un genitore per creare una gerarchia.</p>
            </div>

            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-circle-notch mr-3 w-4"></i> Stato</div>
                    <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-tag mr-3 w-4"></i> Tipologia</div>
                    <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-user-circle mr-3 w-4"></i> Assegnatario</div>
                    <Select v-model="form.assignee_id" :options="assigneeOptions" optionLabel="label" optionValue="value"
                            filter class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="far fa-calendar mr-3 w-4"></i> Scadenza</div>
                    <input type="date" v-model="form.due_date"
                           class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 transition-colors">
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-flag mr-3 w-4"></i> Priorità</div>
                    <Select v-model="form.priority" :options="priorityOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost uppercase text-[10px] font-bold" />
                </div>
            </div>

            <hr class="border-gray-800">

            <div>
                <div class="text-gray-500 text-xs font-bold uppercase mb-3 tracking-widest">Descrizione</div>
                <textarea v-model="form.description" rows="6"
                          class="w-full bg-transparent border-none text-gray-300 placeholder-gray-700 focus:ring-0 resize-none p-0 text-sm leading-relaxed"
                          placeholder="Aggiungi dettagli su questa attività..."></textarea>
                <InputError :message="form.errors.description" />
            </div>

            <div class="bg-gray-800/30 p-5 rounded-2xl border border-gray-800">
                <div class="flex justify-between mb-3">
                    <span class="text-xs text-gray-500 font-bold uppercase tracking-widest">Avanzamento</span>
                    <span class="text-[#07b4f6] font-bold text-xs">{{ form.progress }}%</span>
                </div>
                <input v-model="form.progress" type="range" step="5" class="w-full accent-[#07b4f6] h-1.5 bg-gray-700 rounded-lg appearance-none cursor-pointer">
            </div>

            <div v-if="task" class="pt-8 border-t border-gray-800">
                <div class="text-gray-500 text-xs font-bold uppercase mb-4 tracking-widest">Commenti</div>
                <div class="text-gray-600 italic text-sm text-center py-6 bg-gray-800/20 rounded-2xl border border-dashed border-gray-800">
                    Nessun commento presente.
                </div>
            </div>
        </form>

        <div class="absolute bottom-0 left-0 w-full p-6 bg-gray-900 border-t border-gray-800 flex justify-between items-center">
            <button @click="submit" :disabled="form.processing"
                    class="bg-[#07b4f6] hover:bg-[#06a3de] text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-[#07b4f6]/20 transition-all active:scale-95 disabled:opacity-50">
                <span v-if="form.processing">Salvataggio...</span>
                <span v-else>{{ task ? 'Aggiorna Attività' : 'Crea Attività' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Scoped styles to override PrimeVue defaults for the dark theme */
:deep(.custom-prime-select) {
    background: #1f2937 !important;
    border: 1px solid #374151 !important;
    border-radius: 0.75rem !important;
    color: white !important;
}

:deep(.custom-prime-ghost) {
    background: transparent !important;
    border: none !important;
    color: white !important;
    box-shadow: none !important;
}

:deep(.p-select-panel) {
    background: #111827 !important;
    border: 1px solid #374151 !important;
}

:deep(.p-select-option) {
    color: #9ca3af !important;
}

:deep(.p-select-option:hover) {
    background: #1f2937 !important;
    color: #07b4f6 !important;
}

:deep(.p-select-filter-input) {
    background: #030712 !important;
    border: 1px solid #374151 !important;
    color: white !important;
}
</style>
