<script setup>
import { ref, computed, onMounted, watch, defineAsyncComponent } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useTimeTrackingStore } from '@/stores/timeTrackingStore';

const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));

const page = usePage();
const store = useTimeTrackingStore();

const period = ref('month');
const viewTeam = ref(false);
const loading = ref(false);
const reportData = ref(null);

const isManager = computed(() => page.props.isManager ?? false);

const emit = defineEmits(['open-task']);


const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        background: 'transparent',
        fontFamily: 'inherit',
        toolbar: { show: false },
    },
    theme: { mode: 'dark' },
    colors: ['#07b4f6'],
    xaxis: {
        categories: chartCategories.value,
        labels: { style: { colors: '#9ca3af' } },
    },
    yaxis: {
        labels: {
            style: { colors: '#9ca3af' },
            formatter: (v) => `${v}h`,
        },
    },
    dataLabels: { enabled: false },
    plotOptions: { bar: { borderRadius: 4 } },
    grid: { borderColor: '#374151' },
    tooltip: { y: { formatter: (v) => `${v}h` } },
}));

const chartCategories = computed(() => {
    if (!reportData.value) return [];
    return Object.keys(reportData.value.by_day ?? {}).map(d => {
        return new Date(d).toLocaleDateString('it-IT', { month: 'short', day: 'numeric' });
    });
});

const chartSeries = computed(() => [{
    name: 'Ore',
    data: Object.values(reportData.value?.by_day ?? {}).map(s => parseFloat((s / 3600).toFixed(2))),
}]);

async function loadReport() {
    loading.value = true;
    try {
        reportData.value = await store.fetchReport(period.value, viewTeam.value);
    } catch {
        reportData.value = null;
    } finally {
        loading.value = false;
    }
}

function formatSeconds(s) {
    if (!s) return '0h 0m';
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    return `${h}h ${m}m`;
}

watch([period, viewTeam], loadReport);
onMounted(loadReport);
</script>

<template>
    <div class="space-y-6">
        <!-- Header + controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-xl font-bold text-white">
                <i class="fas fa-clock mr-2 text-[#07b4f6]"></i>
                Tracciamento Tempo
            </h1>

            <div class="flex items-center gap-3">
                <!-- Period selector -->
                <select
                    v-model="period"
                    class="bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-[#07b4f6]"
                >
                    <option value="week">Questa settimana</option>
                    <option value="last_week">Settimana scorsa</option>
                    <option value="month">Questo mese</option>
                    <option value="last_month">Mese scorso</option>
                </select>

                <!-- Team toggle (managers only) -->
                <button
                    v-if="isManager"
                    @click="viewTeam = !viewTeam"
                    :class="[
                        'px-4 py-2 rounded-xl text-sm font-medium transition',
                        viewTeam
                            ? 'bg-[#07b4f6] text-gray-900'
                            : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                    ]"
                >
                    <i class="fas fa-users mr-1"></i>
                    {{ viewTeam ? 'Vista team' : 'Vista personale' }}
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-16 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl"></i>
        </div>

        <template v-else-if="reportData">
            <!-- KPI card -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-800 rounded-2xl p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Totale periodo</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        {{ formatSeconds(reportData.total_seconds) }}
                    </p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Progetti coinvolti</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        {{ Object.keys(reportData.by_project ?? {}).length }}
                    </p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-5">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Voci registrate</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        {{ reportData.entries?.length ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Bar chart: hours by day -->
            <div v-if="chartSeries[0].data.length > 0" class="bg-gray-800 rounded-2xl p-5">
                <p class="text-sm font-semibold text-gray-300 mb-4">Ore per giorno</p>
                <VueApexCharts
                    type="bar"
                    height="220"
                    :options="chartOptions"
                    :series="chartSeries"
                />
            </div>

            <!-- â”€â”€ Personal view: by project breakdown â”€â”€ -->
            <div v-if="!viewTeam && Object.keys(reportData.by_project ?? {}).length > 0" class="bg-gray-800 rounded-2xl p-5">
                <p class="text-sm font-semibold text-gray-300 mb-3">Per progetto</p>
                <ul class="space-y-2">
                    <li
                        v-for="(seconds, projectName) in reportData.by_project"
                        :key="projectName"
                        class="flex justify-between items-center text-sm"
                    >
                        <span class="text-gray-300">{{ projectName }}</span>
                        <span class="text-white font-medium">{{ formatSeconds(seconds) }}</span>
                    </li>
                </ul>
            </div>

            <!-- â”€â”€ Team view: by user breakdown â”€â”€ -->
            <div v-if="viewTeam && reportData.by_user?.length > 0" class="space-y-4">
                <div
                    v-for="userRow in reportData.by_user"
                    :key="userRow.user?.id"
                    class="bg-gray-800 rounded-2xl p-5"
                >
                    <div class="flex justify-between items-center mb-3">
                        <p class="font-semibold text-white">{{ userRow.user?.name }}</p>
                        <p class="text-[#07b4f6] font-medium text-sm">{{ formatSeconds(userRow.total_seconds) }}</p>
                    </div>
                    <ul class="space-y-1">
                        <li
                            v-for="(seconds, projectName) in userRow.by_project"
                            :key="projectName"
                            class="flex justify-between text-xs text-gray-400"
                        >
                            <span>{{ projectName }}</span>
                            <span>{{ formatSeconds(seconds) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Entry table -->
            <div class="bg-gray-800 rounded-2xl overflow-hidden">
                <p class="text-sm font-semibold text-gray-300 px-5 pt-5 pb-3">Voci dettagliate</p>
                <div v-if="reportData.entries?.length === 0" class="px-5 pb-5 text-sm text-gray-500">
                    Nessuna voce nel periodo selezionato.
                </div>
                <table v-else class="w-full text-sm">
                    <thead class="text-xs text-gray-400 uppercase border-b border-gray-700">
                        <tr>
                            <th class="text-left px-5 py-2">Attività</th>
                            <th class="text-left px-5 py-2">Progetto</th>
                            <th v-if="viewTeam" class="text-left px-5 py-2">Utente</th>
                            <th class="text-left px-5 py-2">Data</th>
                            <th class="text-right px-5 py-2">Durata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="entry in reportData.entries"
                            :key="entry.id"
                            class="border-b border-gray-700/50 hover:bg-gray-700/30 group"
                        >
                            <td class="px-5 py-2.5">
                                <button
                                    @click="emit('open-task', { id: entry.task.id, projectId: entry.task.project_id })"
                                    class="text-left text-white hover:text-[#07b4f6] transition-colors font-medium flex items-center gap-1.5"
                                >
                                    <span class="text-[10px] text-gray-500 font-mono">#{{ entry.task?.id }}</span>
                                    <span>{{ entry.task?.title }}</span>
                                </button>
                            </td>
                            <td class="px-5 py-2.5 text-gray-400">{{ entry.task?.project?.name }}</td>
                            <td v-if="viewTeam" class="px-5 py-2.5 text-gray-400">{{ entry.user?.name }}</td>
                            <td class="px-5 py-2.5 text-gray-400">
                                {{ new Date(entry.started_at).toLocaleDateString('it-IT') }}
                            </td>
                            <td class="px-5 py-2.5 text-right text-white font-medium">
                                {{ formatSeconds(entry.duration_seconds) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Error / empty state -->
        <div v-else class="text-center py-16 text-gray-500">
            <i class="fas fa-clock text-4xl mb-3 block"></i>
            Impossibile caricare il report.
        </div>
    </div>
</template>
