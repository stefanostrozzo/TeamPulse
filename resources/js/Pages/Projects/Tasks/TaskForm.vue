<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import InputError from '@/Components/Items/InputError.vue';

/**
 * Component Props
 * @property {Object} project - The parent project context.
 * @property {Object|null} task - The task to edit, or null to create a new one.
 */
const props = defineProps({
    project: { type: Object, required: true },
    task: { type: Object, default: null }
});

const emit = defineEmits(['close', 'confirmDelete']);

/**
 * Inertia form helper initialized with task attributes.
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

/**
 * Syncs the form with the provided task prop or resets it for a new entry.
 * Formats dates to YYYY-MM-DD for standard HTML5 date inputs.
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
        form.type = props.task.type ?? 'feature';
        form.task_parent_id = props.task_parent_id ?? null;

        // Truncate timestamp to date string (ISO 8601)
        form.start_date = props.task.start_date ? props.task.start_date.substring(0, 10) : '';
        form.due_date = props.task.due_date ? props.task.due_date.substring(0, 10) : '';
    } else {
        form.reset();
        form.project_id = props.project.id;
        form.team_id = props.project.team_id;
    }
};

// Initial sync on mount
onMounted(fillForm);

// React to task changes (e.g., when switching selection in the list)
watch(() => props.task, fillForm, { immediate: true, deep: true });

/**
 * Submits the form data to the server using the appropriate HTTP method.
 */
const submit = () => {
    const options = {
        onSuccess: () => {
            emit('close');
        },
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
                <button
                    v-if="task && $page.props.auth.user.permissions.includes('delete tasks')"
                    @click="$emit('confirmDelete', task)"
                    class="p-2 text-gray-500 hover:text-red-500 transition-colors"
                >
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button
                    v-if="(task && $page.props.auth.user.permissions.includes('edit tasks')) || (!task && $page.props.auth.user.permissions.includes('create tasks'))"
                    @click="$emit('close')"
                    class="p-2 text-gray-500 hover:text-white transition-colors"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex-grow p-8 space-y-8 pb-32 overflow-y-auto custom-scrollbar">

            <div>
                <textarea
                    v-model="form.title"
                    rows="1"
                    class="w-full bg-transparent border-none text-3xl font-bold text-white placeholder-gray-700 focus:ring-0 resize-none p-0 overflow-hidden"
                    placeholder="Titolo attività..."
                ></textarea>
                <InputError :message="form.errors.title" />
            </div>

            <div class="mb-6 space-y-2">
                <label class="text-xs font-bold text-gray-500 uppercase tracking-widest ml-1">
                    Attività Superiore (Parent)
                </label>
                <select
                    v-model="form.task_parent_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-[#07b4f6] outline-none transition-all"
                >
                    <option :value="null">Nessuna (Attività principale)</option>
                    <option
                        v-for="availableTask in project.tasks"
                        :key="availableTask.id"
                        :value="availableTask.id"
                        :disabled="availableTask.id === task?.id"
                    >
                        #{{ availableTask.id }} - {{ availableTask.title }}
                    </option>
                </select>
                <p class="text-[10px] text-gray-600 ml-1 italic">
                    Scegli un'attività se questa deve essere una sotto-attività.
                </p>
            </div>

            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-circle-notch mr-3 w-4"></i> Stato</div>
                    <select v-model="form.status" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 transition-colors">
                        <option value="todo">Da Fare</option>
                        <option value="in-progress">In Corso</option>
                        <option value="done">Completato</option>
                        <option value="blocked">Bloccato</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-circle-notch mr-3 w-4"></i> Tipologia</div>
                    <select v-model="form.type" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 transition-colors">
                        <option value="feature">Funzionalità</option>
                        <option value="bug">Bug/Errore</option>
                        <option value="improvement">Miglioramento</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-user-circle mr-3 w-4"></i> Assegnatario </div>
                    <select v-model="form.assignee_id" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 transition-colors">
                        <option :value="null">Non assegnato</option>
                        <option v-for="member in project.members" :key="member.id" :value="member.id">{{ member.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="far fa-calendar mr-3 w-4"></i> Scadenza</div>
                    <input type="date" v-model="form.due_date" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 transition-colors">
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-flag mr-3 w-4"></i> Priorità</div>
                    <select v-model="form.priority" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 uppercase text-[10px] font-bold">
                        <option value="low">Bassa</option>
                        <option value="medium">Media</option>
                        <option value="high">Alta</option>
                    </select>
                </div>
            </div>

            <hr class="border-gray-800">

            <div>
                <div class="text-gray-500 text-xs font-bold uppercase mb-3 tracking-widest">Descrizione</div>
                <textarea
                    v-model="form.description"
                    rows="6"
                    class="w-full bg-transparent border-none text-gray-300 placeholder-gray-700 focus:ring-0 resize-none p-0 text-sm leading-relaxed"
                    placeholder="Aggiungi dettagli su questa attività..."
                ></textarea>
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
                    Ancora nessun commento.
                </div>
            </div>
        </form>

        <div class="absolute bottom-0 left-0 w-full p-6 bg-gray-900 border-t border-gray-800 flex justify-between items-center">
            <button @click="submit" :disabled="form.processing"
                    class="bg-[#07b4f6] hover:bg-[#06a3de] text-white px-8 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-[#07b4f6]/20 transition-all active:scale-95 disabled:opacity-50">
                <span v-if="form.processing">Salvataggio...</span>
                <span v-else>{{ task ? 'Aggiorna' : 'Crea Attività' }}</span>
            </button>
        </div>
    </div>
</template>
