<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { onMounted, watch, computed, ref } from 'vue';
import InputError from '@/Components/Items/InputError.vue';
import Select from 'primevue/select';
import CommentEditor from '../Comments/CommentEditor.vue';
import CommentItem from '../Comments/CommentItem.vue';
import TextInput from '@/Components/Items/TextInput.vue';

/**
 * Component Props
 * project: Context containing members and tasks for validation and selection.
 * task: The task instance (null when creating a new task).
 */
const props = defineProps({
    project: { type: Object, required: true },
    task: { type: Object, default: null }
});

const updatedTask = computed(() => {
    return props.project.tasks.find(t => t.id === props.task?.id) || props.task;
});

const emit = defineEmits(['close', 'confirmDelete']);

/**
 * Initialize form state using Inertia's useForm helper.
 */
const form = useForm({
    project_id: props.project.id,
    team_id: props.project.team_id,
    title: '',
    description: '',
    status: 'todo',
    priority: 'low',
    type: 'feature',
    assignee_id: props.task?.assignee_id ?? null,
    start_date: '',
    due_date: '',
    progress: 0,
    task_parent_id: props.task?.task_parent_id ?? null,
});

// Localization: UI Labels for Italian users
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
 * Compute available assignees, including a null option for unassigned tasks.
 */
const assigneeOptions = computed(() => {
    const members = props.project.members.map(m => ({
        label: m.name,
        value: m.id
    }));
    return [{ label: 'Nessun assegnatario', value: null }, ...members];
});

/**
 * Persist a new comment to the database.
 */
const isAddingComment = ref(false);
const saveComment = (htmlContent) => {
    // Ensure the task exists before attempting to comment
    if (!props.task?.id) return;

    router.post(route('comments.store', props.task.id), {
        content: htmlContent
    }, {
        onSuccess: () => {
            isAddingComment.value = false;
        },
        preserveScroll: true
    });
};

/**
 * Prevent circular parent-child relationships by filtering out
 * the current task and its descendants.
 */
const availableParentOptions = computed(() => {
    let tasks = props.project.tasks || [];
    if (props.task) {
        // Recursive check to identify all sub-task IDs
        const getIds = (id, list) => {
            return list.filter(t => t.task_parent_id === id)
                .reduce((acc, curr) => [...acc, curr.id, ...getIds(curr.id, list)], []);
        };
        const illegalIds = [props.task.id, ...getIds(props.task.id, tasks)];
        tasks = tasks.filter(t => !illegalIds.includes(t.id));
    }
    return [
        { label: 'Nessuna (Attività principale)', value: null },
        ...tasks.map(t => ({ label: `#${t.id} - ${t.title}`, value: t.id }))
    ];
});

/**
 * Populate form with existing task data or reset to defaults.
 */
const fillForm = () => {
    if (props.task) {
        Object.assign(form, {
            title: props.task.title ?? '',
            description: props.task.description ?? '',
            status: props.task.status ?? 'todo',
            priority: props.task.priority ?? 'low',
            type: props.task.type ?? 'feature',
            assignee_id: props.task.assignee_id ?? null,
            progress: props.task.progress ?? 0,
            task_parent_id: props.task.task_parent_id ?? null,
            start_date: props.task.start_date?.substring(0, 10) ?? '',
            due_date: props.task.due_date?.substring(0, 10) ?? '',
        });
    } else {
        form.reset();
    }
};

onMounted(fillForm);
watch(() => props.task, fillForm, { deep: true });

const submit = () => {
    const options = { onSuccess: () => emit('close'), preserveScroll: true };
    props.task?.id ? form.put(route('tasks.update', props.task.id), options)
        : form.post(route('tasks.store'), options);
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

        <form @submit.prevent="submit" class="grow p-8 space-y-8 pb-32 overflow-y-auto custom-scrollbar">
            <div>
                <textarea v-model="form.title" rows="1"
                          class="w-full bg-transparent border-none text-3xl font-bold text-white placeholder-gray-700 focus:ring-0 resize-none p-0 overflow-hidden"
                          placeholder="Titolo attività..."></textarea>
                <InputError :message="form.errors.title" />
            </div>

            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="fas fa-sitemap mr-3 w-4"></i> Genitore
                    </div>
                    <Select v-model="form.task_parent_id"
                            :options="availableParentOptions"
                            optionLabel="label"
                            optionValue="value"
                            filter
                            placeholder="Nessuna attività principale"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="fas fa-circle-notch mr-3 w-4"></i> Stato
                    </div>
                    <Select v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="fas fa-tag mr-3 w-4"></i> Tipologia
                    </div>
                    <Select v-model="form.type" :options="typeOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="fas fa-user-circle mr-3 w-4"></i> Assegnatario
                    </div>
                    <Select v-model="form.assignee_id"
                            :options="assigneeOptions"
                            optionLabel="label"
                            optionValue="value"
                            filter
                            placeholder="Nessun assegnatario"
                            class="col-span-2 custom-prime-ghost" />
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="far fa-calendar mr-3 w-4"></i> Scadenza
                    </div>
                    <TextInput type="date" v-model="form.due_date"
                           class="col-span-2 bg-transparent border-none text-white focus:ring-0 p-0 hover:bg-gray-800/50 transition-colors cursor-pointer outline-none"/>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center">
                        <i class="fas fa-flag mr-3 w-4"></i> Priorità
                    </div>
                    <Select v-model="form.priority" :options="priorityOptions" optionLabel="label" optionValue="value"
                            class="col-span-2 custom-prime-ghost uppercase text-[10px]" />
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
                <div class="flex items-center justify-between mb-6">
                    <div class="text-gray-500 text-xs font-bold uppercase tracking-widest">Commenti</div>

                    <button v-if="$page.props.auth.user.permissions.includes('create tasks') && !isAddingComment"
                            type="button"
                            @click.prevent="isAddingComment = true"
                            class="flex items-center gap-2 text-[#07b4f6] hover:text-white transition-colors text-xs font-bold uppercase">
                        <i class="fas fa-plus"></i> Aggiungi Commento
                    </button>
                </div>

                <CommentEditor
                    v-if="isAddingComment"
                    @save="saveComment"
                    @cancel="isAddingComment = false"
                />

                <div v-if="!task.comments?.length"
                     class="text-gray-600 italic text-sm text-center py-6 bg-gray-800/20 rounded-2xl border border-dashed border-gray-800">
                    Ancora nessun commento.
                </div>

                <div v-else class="space-y-4">
                    <CommentItem
                        v-for="comment in updatedTask.comments"
                        :key="comment.id"
                        :comment="comment"
                    />
                </div>
            </div>
        </form>

        <div class="absolute bottom-0 left-0 w-full p-6 bg-gray-900 border-t border-gray-800 flex justify-between items-center">
            <button v-if="$page.props.auth.user.permissions.includes('edit tasks')" @click="submit" :disabled="form.processing"
                    class="bg-[#07b4f6] hover:bg-[#06a3de] text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-[#07b4f6]/20 transition-all active:scale-95 disabled:opacity-50">
                <span v-if="form.processing">Salvataggio...</span>
                <span v-else>{{ task ? 'Aggiorna Attività' : 'Crea Attività' }}</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
.custom-prime-ghost {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

.custom-prime-ghost {
    padding: 0 !important; /* Rimuove il padding interno per allinearsi all'input date p-0 */
    color: #f3f4f6 !important; /* text-gray-100 */
}

/* Rimuove l'icona della freccia se vuoi un look minimale "inline edit" */
.custom-prime-ghost {
    display: none !important;
}

/* Stile per l'input date nativo per farlo somigliare a PrimeVue */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.5;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
</style>
