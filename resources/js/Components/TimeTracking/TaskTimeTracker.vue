<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useTimeTrackingStore } from '@/stores/timeTrackingStore';
import { storeToRefs } from 'pinia';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    task: { type: Object, required: true },
    teamMembers: { type: Array, default: () => [] },
    /** Whether the current user is a manager (can log on behalf) */
    isManager: { type: Boolean, default: false },
});

const store = useTimeTrackingStore();
const toast = useToast();
const { activeTimer, hasActiveTimer, activeTaskId, elapsedFormatted } = storeToRefs(store);

const entries = ref([]);
const taskTimeSpent = ref(props.task.time_spent ?? 0);
const estimatedHours = ref(props.task.estimated_hours ?? 0);
const loading = ref(false);

// Manual log form
const showManualForm = ref(false);
const manualForm = ref({
    started_at: '',
    ended_at: '',
    description: '',
    user_id: null,
});

watch(showManualForm, (val) => {
    if (val) {
        console.log('Opening manual form for task:', props.task.id);
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const today = `${year}-${month}-${day}`;

        // Propose current day, but set time to 00:00 (user will adjust)
        manualForm.value.started_at = `${today}T00:00`;
        manualForm.value.ended_at = `${today}T00:00`;
    }
});

/** True when the active timer is running on THIS specific task. */
const timerOnThisTask = computed(() => activeTaskId.value === props.task.id);

/** Display total logged hours, formatted to 1 decimal. */
const totalHoursFormatted = computed(() => {
    return taskTimeSpent.value.toFixed(1) + 'h';
});

async function loadEntries() {
    loading.value = true;
    try {
        const res = await fetch(route('time.index', { task: props.task.id }), {
            headers: { 'Accept': 'application/json' },
        });
        const data = await res.json();
        entries.value = data.entries;
        taskTimeSpent.value = data.time_spent ?? 0;
        estimatedHours.value = data.estimated_hours ?? 0;
    } catch {
        toast.add({ severity: 'error', summary: 'Errore', detail: 'Impossibile caricare i dati sul tempo.', life: 4000 });
    } finally {
        loading.value = false;
    }
}

async function handleTimerToggle() {
    console.log('Toggling timer for task:', props.task.id);
    try {
        if (timerOnThisTask.value) {
            const finalDuration = elapsedFormatted.value;
            const stopped = await store.stopTimer();
            if (stopped) {
                entries.value.unshift(stopped);
                taskTimeSpent.value += stopped.duration_seconds / 3600;
            }
            toast.add({ severity: 'success', summary: 'Timer fermato', detail: `Durata: ${finalDuration}`, life: 3000 });
        } else {
            await store.startTimer(props.task.id);
            toast.add({ severity: 'info', summary: 'Timer avviato', life: 2000 });
        }
    } catch {
        toast.add({ severity: 'error', summary: 'Errore timer', life: 3000 });
    }
}

async function submitManualEntry() {
    try {
        const payload = { ...manualForm.value };
        if (!props.isManager || !payload.user_id) delete payload.user_id;

        const entry = await store.logManual(props.task.id, payload);
        entries.value.unshift(entry);
        taskTimeSpent.value += entry.duration_seconds / 3600;
        showManualForm.value = false;
        manualForm.value = { started_at: '', ended_at: '', description: '', user_id: null };
        toast.add({ severity: 'success', summary: 'Tempo registrato', life: 3000 });
    } catch {
        toast.add({ severity: 'error', summary: 'Errore salvataggio', life: 3000 });
    }
}

async function removeEntry(entry) {
    try {
        await store.deleteEntry(entry.id);
        entries.value = entries.value.filter(e => e.id !== entry.id);
        taskTimeSpent.value -= (entry.duration_seconds ?? 0) / 3600;
        toast.add({ severity: 'success', summary: 'Voce eliminata', life: 2000 });
    } catch {
        toast.add({ severity: 'error', summary: 'Errore eliminazione', life: 3000 });
    }
}

/** Format seconds into "Xh Ym" string. */
function formatDuration(seconds) {
    if (!seconds) return '0m';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    return h > 0 ? `${h}h ${m}m` : `${m}m`;
}

onMounted(loadEntries);
</script>

<template>
    <div class="space-y-4">
        <!-- Header: total time + timer button -->
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400">Tempo registrato</p>
                <p class="text-lg font-semibold text-white">
                    {{ totalHoursFormatted }}
                    <span v-if="estimatedHours > 0" class="text-sm text-gray-400">
                        / {{ estimatedHours }}h stimato
                    </span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <!-- Live elapsed display when timer is on this task -->
                <span v-if="timerOnThisTask" class="font-mono text-[#07b4f6] text-sm">
                    {{ elapsedFormatted }}
                </span>

                <button
                    type="button"
                    @click="handleTimerToggle"
                    :class="[
                        'flex items-center gap-1 px-3 py-1.5 rounded-xl text-sm font-medium transition',
                        timerOnThisTask
                            ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30'
                            : hasActiveTimer
                                ? 'bg-yellow-500/20 text-yellow-400 hover:bg-yellow-500/30'
                                : 'bg-[#07b4f6]/20 text-[#07b4f6] hover:bg-[#07b4f6]/30',
                    ]"
                    :title="hasActiveTimer && !timerOnThisTask ? 'FermerÃ  il timer corrente e ne avvierÃ  uno nuovo' : ''"
                >
                    <i :class="timerOnThisTask ? 'fas fa-stop' : 'fas fa-play'" class="text-xs"></i>
                    {{ timerOnThisTask ? 'Ferma' : 'Avvia' }}
                </button>

                <button
                    type="button"
                    @click="showManualForm = !showManualForm"
                    class="flex items-center gap-1 px-3 py-1.5 rounded-xl text-sm font-medium bg-gray-700 text-gray-300 hover:bg-gray-600 transition"
                >
                    <i class="fas fa-plus text-xs"></i>
                    Manuale
                </button>
            </div>
        </div>

        <!-- Manual log form -->
        <div v-if="showManualForm" class="bg-gray-800 rounded-xl p-4 space-y-3">
            <p class="text-sm font-medium text-gray-300">Registra tempo manualmente</p>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Inizio</label>
                    <input
                        v-model="manualForm.started_at"
                        type="datetime-local"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-[#07b4f6]"
                    />
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Fine</label>
                    <input
                        v-model="manualForm.ended_at"
                        type="datetime-local"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-[#07b4f6]"
                    />
                </div>
            </div>

            <input
                v-model="manualForm.description"
                type="text"
                placeholder="Descrizione (opzionale)"
                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-[#07b4f6]"
            />

            <!-- On-behalf selector (managers only) -->
            <div v-if="isManager && teamMembers.length > 0">
                <label class="text-xs text-gray-400 mb-1 block">Per conto di</label>
                <select
                    v-model="manualForm.user_id"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-[#07b4f6]"
                >
                    <option :value="null">Me stesso</option>
                    <option v-for="m in teamMembers" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" @click="showManualForm = false" class="px-3 py-1.5 text-sm text-gray-400 hover:text-white transition">
                    Annulla
                </button>
                <button
                    type="button"
                    @click="submitManualEntry"
                    class="px-4 py-1.5 text-sm font-medium bg-[#07b4f6] text-gray-900 rounded-xl hover:bg-[#07b4f6]/80 transition"
                >
                    Salva
                </button>
            </div>
        </div>

        <!-- Entry list -->
        <div v-if="loading" class="text-center text-gray-500 text-sm py-4">
            <i class="fas fa-spinner fa-spin"></i> Caricamento...
        </div>

        <div v-else-if="entries.length === 0" class="text-center text-gray-500 text-sm py-4">
            Nessun tempo registrato per questa attività.
        </div>

        <ul v-else class="space-y-2">
            <li
                v-for="entry in entries"
                :key="entry.id"
                class="flex items-center justify-between bg-gray-800 rounded-xl px-4 py-2.5"
            >
                <div>
                    <p class="text-sm text-white font-medium">{{ formatDuration(entry.duration_seconds) }}</p>
                    <p class="text-xs text-gray-400">
                        {{ entry.user?.name }} -
                        {{ new Date(entry.started_at).toLocaleDateString('it-IT') }}
                        <span v-if="entry.description"> Â· {{ entry.description }}</span>
                    </p>
                </div>
                <button
                    type="button"
                    @click="removeEntry(entry)"
                    class="text-gray-500 hover:text-red-400 transition text-xs p-1"
                    title="Elimina"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </li>
        </ul>
    </div>
</template>
