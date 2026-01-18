<script setup>
import { useForm } from '@inertiajs/vue3';

/**
 * Component Props
 * @property {Object|null} team - If provided, the component acts in "edit" mode. If null, it's "create" mode.
 */
const props = defineProps({
    team: {
        type: Object,
        default: null,
    },
});

/**
 * Emitted events to notify the parent component
 */
const emit = defineEmits(['close', 'confirmDelete']);

/**
 * Inertia Form helper for reactive data binding and XHR submission
 */
const form = useForm({
    name: props.team ? props.team.name : '',
});

/**
 * Determine the submission logic based on the presence of a team object
 */
const submit = () => {
    if (props.team) {
        // Update existing team
        form.put(route('teams.update', props.team.id), {
            onSuccess: () => emit('close'),
            preserveScroll: true,
        });
    } else {
        // Create new team
        form.post(route('teams.store'), {
            onSuccess: () => emit('close'),
            preserveScroll: true,
        });
    }
};

/**
 * Handle form cancellation
 */
const cancel = () => {
    emit('close');
};
</script>

<template>
    <div class="edit-team-container">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white tracking-tight">
                {{ team ? 'Modifica Team' : 'Crea Nuovo Team' }}
            </h2>
            <p class="text-sm text-gray-400 mt-1">
                {{ team ? 'Aggiorna le informazioni del tuo spazio di lavoro.' : 'Inizia a collaborare creando uno spazio per i tuoi progetti.' }}
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Nome del Team
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    placeholder="Es. Team Sviluppo, Marketing..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#07b4f6] focus:border-transparent transition-all"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-400">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                <button
                    v-if="team && team.can_delete"
                    type="button"
                    @click="$emit('confirmDelete', team)"
                    class="px-6 py-2.5 text-sm font-semibold text-red-500 hover:text-red-400 hover:bg-red-500/10 rounded-xl transition-all"
                >
                    <i class="fas fa-trash-alt mr-2"></i> Elimina Team
                </button>

                <button
                    type="submit"
                    v-if="team.can_manage_team"
                    :disabled="form.processing"
                    class="flex items-center gap-2 px-6 py-2 rounded-xl text-sm font-bold text-white bg-[#07b4f6] hover:bg-[#06a3dd] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-lg shadow-[#07b4f6]/20"
                >
                    <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                    {{ team ? 'Salva Modifiche' : 'Crea Team' }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Scoped transitions or specific tweaks if needed */
.edit-team-container {
    @apply transition-all duration-300;
}
</style>
