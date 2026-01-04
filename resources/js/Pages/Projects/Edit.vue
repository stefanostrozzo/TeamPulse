<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import TextInput from '@/Components/Items/TextInput.vue';
import InputError from '@/Components/Items/InputError.vue';

/**
 * Component Props
 * @property {Object|null} project - Provided for 'Edit' mode, null for 'Create' mode.
 * @property {Array} customers - List of customers from DashboardController.
 */
const props = defineProps({
    project: {
        type: Object,
        default: null
    },
    currentTeamId: {
        type: Number,
        required: true
    }
});

console.log(props.currentTeamId);

const emit = defineEmits(['close', 'confirmDelete']);

/**
 * Inertia Form Helper
 * Maps the form fields to the Project model fillable attributes.
 */
const form = useForm({
    team_id: props.project ? props.project.team_id : props.currentTeamId,
    name: '',
    description: '',
    status: 'planning',
    priority: 'medium',
    start_date: '',
    end_date: '',
    progress: 0,
});

/**
 * Sync form data when the project prop changes or on mount.
 * Formats dates to YYYY-MM-DD for compatibility with HTML5 date inputs.
 */
const fillForm = () => {
    if (props.project) {
        form.team_id = props.project.team_id;
        form.name = props.project.name ?? '';
        form.description = props.project.description ?? '';
        form.status = props.project.status ?? 'planning';
        form.priority = props.project.priority ?? 'medium';
        form.start_date = props.project.start_date ? new Date(props.project.start_date).toISOString().split('T')[0] : '';
        form.end_date = props.project.end_date ? new Date(props.project.end_date).toISOString().split('T')[0] : '';
        form.progress = props.project.progress ?? 0;
    } else {
        form.reset();
        form.team_id = props.currentTeamId;
    }
};

onMounted(fillForm);
watch(() => props.project, fillForm);

/**
 * Submit the form.
 * Uses POST for new projects and PUT for existing ones.
 */
const submit = () => {
    const options = {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
        preserveScroll: true,
    };

    if (props.project?.id) {
        form.put(route('project.update', props.project.id), options);
    } else {
        form.post(route('project.store'), options);
    }
};
</script>

<template>
    <div class="p-8 text-white">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold tracking-tight">
                {{ project ? 'Modifica Progetto' : 'Nuovo Progetto' }}
            </h2>
            <button @click="$emit('close')" class="text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-400 mb-2">Nome Progetto</label>
                <TextInput
                    v-model="form.name"
                    type="text"
                    class="w-full"
                    required
                    autofocus
                    placeholder="Esempio: Restyling Sito Web"
                />
                <InputError :message="form.errors.name" class="mt-1" />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-400 mb-2">Descrizione</label>
                <textarea v-model="form.description" rows="3"
                          class="w-full bg-gray-800 border-gray-700 rounded-xl text-white focus:border-[#07b4f6] focus:ring-0 transition-all"></textarea>
                <InputError :message="form.errors.description" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Stato</label>
                <select v-model="form.status" class="w-full bg-gray-800 border-gray-700 rounded-xl text-white focus:border-[#07b4f6] focus:ring-0 transition-all" required>
                    <option value="active">Attivo</option>
                    <option value="on-hold">In pausa</option>
                    <option value="completed">Completato</option>
                    <option value="archived">Archiviato</option>
                </select>
                <InputError :message="form.errors.status" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Priorità</label>
                <select v-model="form.priority"
                        class="w-full bg-gray-800 border-gray-700 rounded-xl text-white focus:border-[#07b4f6] focus:ring-0 transition-all" required>
                    <option value="low">Bassa</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                </select>
                <InputError :message="form.errors.priority" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Data Inizio</label>
                <TextInput v-model="form.start_date" type="date" class="w-full" />
                <InputError :message="form.errors.start_date" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Scadenza</label>
                <TextInput v-model="form.end_date" type="date" class="w-full" />
                <InputError :message="form.errors.end_date" class="mt-1" />
            </div>

            <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-medium text-gray-400">Progresso Completamento ({{ form.progress }}%)</label>
                </div>
                <input v-model="form.progress" type="range" min="0" max="100" step="10"
                       class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-[#07b4f6]">
                <InputError :message="form.errors.progress" class="mt-1" />
            </div>

            <div class="md:col-span-2 flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-800">
                <button
                    v-if="project"
                    type="button"
                    @click="$emit('confirmDelete', project)"
                    class="px-6 py-2.5 text-sm font-semibold text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all"
                >
                    <i class="fas fa-trash-alt mr-2"></i> Elimina Progetto
                </button>
                <button type="submit" :disabled="form.processing"
                        class="px-8 py-2.5 bg-gradient-to-r from-[#07b4f6] to-cyan-500 text-white rounded-xl font-bold shadow-lg shadow-[#07b4f6]/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-50">
                    <span v-if="form.processing"><i class="fas fa-spinner fa-spin mr-2"></i> Salvataggio...</span>
                    <span v-else>{{ project ? 'Aggiorna Progetto' : 'Crea Progetto' }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
}
</style>
