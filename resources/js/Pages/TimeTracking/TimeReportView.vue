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

// Nuovi stati di filtraggio specifici
const projectId = ref('');
const startDate = ref('');
const endDate = ref('');

const isManager = computed(() => page.props.isManager ?? false);
const emit = defineEmits(['open-task']);

// Elenco dei progetti del team estratti dalla chiamata report
const projectsList = computed(() => reportData.value?.projects ?? []);

// Monitora se i filtri manuali di data sono attivi
const isCustomRange = computed(() => !!(startDate.value && endDate.value));

// Opzioni grafici del tempo per giorno
const chartOptionsByDay = computed(() => ({
    chart: {
        type: 'bar',
        background: 'transparent',
        fontFamily: 'inherit',
        toolbar: { show: false },
    },
    theme: { mode: 'dark' },
    colors: ['#07b4f6'],
    xaxis: {
        categories: chartCategoriesByDay.value,
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

const chartCategoriesByDay = computed(() => {
    if (!reportData.value) return [];
    return Object.keys(reportData.value.by_day ?? {}).map(d => {
        return new Date(d).toLocaleDateString('it-IT', { month: 'short', day: 'numeric' });
    });
});

const chartSeriesByDay = computed(() => [{
    name: 'Ore',
    data: Object.values(reportData.value?.by_day ?? {}).map(s => parseFloat((s / 3600).toFixed(2))),
}]);

// OPZIONI GRAFICO TOTALI DI PROGETTO (RICHIESTA UTENTE)
const projectChartCategories = computed(() => {
    if (!reportData.value || !reportData.value.by_project) return [];
    return Object.keys(reportData.value.by_project);
});

const projectChartSeries = computed(() => [{
    name: 'Ore Totali Progetto',
    data: Object.values(reportData.value?.by_project ?? {}).map(s => parseFloat((s / 3600).toFixed(2))),
}]);

const projectChartOptions = computed(() => ({
    chart: {
        type: 'bar',
        background: 'transparent',
        fontFamily: 'inherit',
        toolbar: { show: false },
    },
    theme: { mode: 'dark' },
    // Colori vibranti e distinti per rendere il grafico a barre più chiaro
    colors: ['#07b4f6', '#10b981', '#6366f1', '#f59e0b', '#ec4899', '#8b5cf6', '#ef4444', '#14b8a6'],
    xaxis: {
        categories: projectChartCategories.value,
        labels: { 
            style: { colors: '#9ca3af', fontSize: '11px' },
            rotate: -20,
            trim: true
        },
    },
    yaxis: {
        labels: {
            style: { colors: '#9ca3af' },
            formatter: (v) => `${v}h`,
        },
    },
    dataLabels: { 
        enabled: true,
        formatter: (v) => `${v}h`,
        style: { 
            fontSize: '10px',
            colors: ['#fff']
        },
        background: {
            enabled: true,
            foreColor: '#000',
            padding: 3,
            borderRadius: 4,
            borderWidth: 1,
            borderColor: '#374151',
            opacity: 0.8
        }
    },
    // Ogni barra assume un colore diverso dall'array colors
    plotOptions: { 
        bar: { 
            borderRadius: 6,
            distributed: true,
            columnWidth: '40%'
        } 
    },
    grid: { borderColor: '#374151' },
    // Effetto hover ultra chiaro e informativo
    tooltip: { 
        y: { 
            formatter: (v) => `${v} ore registrate` 
        } 
    },
    legend: {
        show: true,
        position: 'bottom',
        labels: { colors: '#9ca3af' },
        itemMargin: { horizontal: 10, vertical: 5 }
    }
}));

async function loadReport() {
    loading.value = true;
    try {
        let url = route('time.report') + `?period=${period.value}&team=${viewTeam.value ? 1 : 0}`;
        
        if (projectId.value) {
            url += `&project_id=${projectId.value}`;
        }
        
        if (startDate.value && endDate.value) {
            url += `&start_date=${startDate.value}&end_date=${endDate.value}`;
        }

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' },
        });

        if (!res.ok) throw new Error(await res.text());
        reportData.value = await res.json();
    } catch (err) {
        console.error(err);
        reportData.value = null;
    } finally {
        loading.value = false;
    }
}

// Genera ed apre la pagina del report di stampa in una scheda separata
function generatePrintableReport() {
    let url = route('time.report.print') + `?period=${period.value}&team=${viewTeam.value ? 1 : 0}`;
    if (projectId.value) {
        url += `&project_id=${projectId.value}`;
    }
    if (startDate.value && endDate.value) {
        url += `&start_date=${startDate.value}&end_date=${endDate.value}`;
    }
    window.open(url, '_blank');
}

// Reimposta tutti i filtri di ricerca portando la data a predefinita
function resetFilters() {
    projectId.value = '';
    startDate.value = '';
    endDate.value = '';
    period.value = 'month';
    loadReport();
}

// Chiama l'API al cambio dei selettori period, team o filtri avanzati
watch([period, viewTeam, projectId], () => {
    // Se si attiva la selezione per periodo prefissato, pulisci le date libere
    if (period.value !== 'custom') {
        // evita loop
        if (startDate.value || endDate.value) {
            startDate.value = '';
            endDate.value = '';
        }
    }
    loadReport();
});

// Al cambiamento interno di data personalizzata
watch([startDate, endDate], () => {
    if (startDate.value && endDate.value) {
        period.value = 'custom';
        loadReport();
    }
});

function formatSeconds(s) {
    if (!s) return '0h 0m';
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    return `${h}h ${m}m`;
}

onMounted(loadReport);
</script>

<template>
    <div class="space-y-6">
        <!-- Header + controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-xl font-bold text-white">
                <i class="fas fa-clock mr-2 text-[#07b4f6]"></i>
                Tracciamento Tempo & Report Ore
            </h1>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Reset button se qualsiasi filtro è attivo -->
                <button
                    v-if="projectId || startDate || endDate || period !== 'month'"
                    @click="resetFilters"
                    class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-semibold rounded-xl px-3 py-2 transition flex items-center gap-1"
                    title="Annulla tutti i filtri"
                >
                    <i class="fas fa-undo"></i> Reset
                </button>

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

                <!-- Pulsante generate report per fattura -->
                <button
                    @click="generatePrintableReport"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl px-4 py-2 shadow-sm transition flex items-center gap-2"
                >
                    <i class="fas fa-file-invoice"></i> Genera Report
                </button>
            </div>
        </div>

        <!-- Modulo di Ricerca Avanzata -->
        <div class="bg-gray-800 rounded-2xl p-5 border border-gray-700/60 shadow-lg">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-1.5">
                <i class="fas fa-sliders-h text-[#07b4f6]"></i> Filtri di Ricerca Avanzati
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <!-- Dropdown Progetto -->
                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs text-gray-300 font-semibold">Progetto</label>
                    <select
                        v-model="projectId"
                        class="bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-[#07b4f6] w-full"
                    >
                        <option value="">Tutti i progetti</option>
                        <option v-for="proj in projectsList" :key="proj.id" :value="proj.id">
                            {{ proj.name }}
                        </option>
                    </select>
                </div>

                <!-- Selettore Periodo standard (se non attivo un custom date range) -->
                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs text-gray-300 font-semibold">Intervallo Predefinito</label>
                    <select
                        v-model="period"
                        class="bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-[#07b4f6] w-full"
                    >
                        <option value="week">Questa settimana</option>
                        <option value="last_week">Settimana scorsa</option>
                        <option value="month">Questo mese</option>
                        <option value="last_month">Mese scorso</option>
                        <option value="custom" disabled>Intervallo personalizzato</option>
                    </select>
                </div>

                <!-- Data Inizio -->
                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs text-gray-300 font-semibold">Dal Giorno</label>
                    <input
                        type="date"
                        v-model="startDate"
                        class="bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-[#07b4f6] w-full"
                    />
                </div>

                <!-- Data Fine -->
                <div class="flex flex-col space-y-1.5">
                    <label class="text-xs text-gray-300 font-semibold">Al Giorno</label>
                    <input
                        type="date"
                        v-model="endDate"
                        class="bg-gray-900 border border-gray-700 text-white text-sm rounded-xl px-3 py-2 focus:outline-none focus:border-[#07b4f6] w-full"
                    />
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-16 text-gray-500">
            <i class="fas fa-spinner fa-spin text-2xl"></i>
        </div>

        <template v-else-if="reportData">
            <!-- KPI card -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Totale Ore Periodo</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        {{ formatSeconds(reportData.total_seconds) }}
                    </p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Progetti coinvolti</p>
                    <p class="text-2xl font-bold text-[#07b4f6] mt-1">
                        {{ Object.keys(reportData.by_project ?? {}).length }}
                    </p>
                </div>

                <div class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Voci registrate</p>
                    <p class="text-2xl font-bold text-white mt-1">
                        {{ reportData.entries?.length ?? 0 }}
                    </p>
                </div>
            </div>

            <!-- Blocchi Grafici - Selezionabili affiancati / impilati -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Nuovo grafico: Ripartizione Ore Totali per Progetto (Distributed Bars con legenda ed hover) -->
                <div v-if="projectChartCategories.length > 0" class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                    <p class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-1.5">
                        <i class="fas fa-chart-pie text-emerald-500"></i> Ore Totali per Progetto
                    </p>
                    <VueApexCharts
                        type="bar"
                        height="240"
                        :options="projectChartOptions"
                        :series="projectChartSeries"
                    />
                </div>

                <!-- Grafico originale migliorato: Ore per Giorno -->
                <div v-if="chartSeriesByDay[0].data.length > 0" class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                    <p class="text-sm font-semibold text-gray-300 mb-4 flex items-center gap-1.5">
                        <i class="fas fa-chart-bar text-cyan-400"></i> Carico di Lavoro Giornaliero (Ore)
                    </p>
                    <VueApexCharts
                        type="bar"
                        height="240"
                        :options="chartOptionsByDay"
                        :series="chartSeriesByDay"
                    />
                </div>
            </div>

            <!-- Personal view: list table -->
            <div v-if="!viewTeam && Object.keys(reportData.by_project ?? {}).length > 0" class="bg-gray-800 rounded-2xl p-5 border border-gray-750">
                <p class="text-sm font-semibold text-gray-300 mb-3">Rendimento per Progetto (Tabella)</p>
                <ul class="divide-y divide-gray-700">
                    <li
                        v-for="(seconds, projectName) in reportData.by_project"
                        :key="projectName"
                        class="flex justify-between items-center py-2.5 text-sm"
                    >
                        <span class="text-gray-300 font-medium flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#07b4f6]"></span>
                            {{ projectName }}
                        </span>
                        <span class="text-white font-bold bg-gray-900 px-3 py-1 rounded-lg border border-gray-700">
                            {{ formatSeconds(seconds) }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Team view: by user breakdown -->
            <div v-if="viewTeam && reportData.by_user?.length > 0" class="space-y-4">
                <p class="text-sm font-semibold text-gray-300">Rendimento per Membro Team</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="userRow in reportData.by_user"
                        :key="userRow.user?.id"
                        class="bg-gray-800 rounded-2xl p-5 border border-gray-750/70 shadow-sm"
                    >
                        <div class="flex justify-between items-center mb-3">
                            <p class="font-bold text-white flex items-center gap-1.5">
                                <i class="fas fa-user-circle text-gray-400"></i> {{ userRow.user?.name }}
                            </p>
                            <p class="text-[#07b4f6] font-bold text-sm bg-gray-950 px-2.5 py-1 rounded-lg">
                                {{ formatSeconds(userRow.total_seconds) }}
                            </p>
                        </div>
                        <ul class="space-y-1.5 border-t border-gray-700/60 pt-2">
                            <li
                                v-for="(seconds, projectName) in userRow.by_project"
                                :key="projectName"
                                class="flex justify-between text-xs text-gray-400"
                            >
                                <span>{{ projectName }}</span>
                                <span class="font-medium text-gray-300">{{ formatSeconds(seconds) }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Entry table -->
            <div class="bg-gray-800 rounded-2xl overflow-hidden border border-gray-750 shadow-md">
                <p class="text-sm font-semibold text-gray-300 px-5 pt-5 pb-3">Registro Dettagliato Attività</p>
                <div v-if="reportData.entries?.length === 0" class="px-5 pb-5 text-sm text-gray-500">
                    Nessuna voce trovata per l'intervallo o progetto selezionato.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-400 uppercase border-b border-gray-700 bg-gray-850">
                            <tr>
                                <th class="text-left px-5 py-3">Task / Attività</th>
                                <th class="text-left px-5 py-3">Progetto</th>
                                <th v-if="viewTeam" class="text-left px-5 py-3">Utente</th>
                                <th class="text-left px-5 py-3">Data Log</th>
                                <th class="text-right px-5 py-3">Tempo Dedicato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <tr
                                v-for="entry in reportData.entries"
                                :key="entry.id"
                                class="hover:bg-gray-750 transition-colors group"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex flex-col">
                                        <button
                                            @click="emit('open-task', { id: entry.task.id, projectId: entry.task.project_id })"
                                            class="text-left text-white hover:text-[#07b4f6] transition-colors font-semibold flex items-center gap-1.5"
                                        >
                                            <span class="text-[10px] text-gray-500 font-mono">#{{ entry.task?.id }}</span>
                                            <span>{{ entry.task?.title }}</span>
                                        </button>
                                        <!-- Descrizione specifica se presente -->
                                        <span v-if="entry.description" class="text-xs text-gray-400 italic mt-1.5 bg-gray-900 border border-gray-700/55 p-2 rounded-lg max-w-xl">
                                            {{ entry.description }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-gray-300 whitespace-nowrap">{{ entry.task?.project?.name }}</td>
                                <td v-if="viewTeam" class="px-5 py-4 text-gray-350 whitespace-nowrap">{{ entry.user?.name }}</td>
                                <td class="px-5 py-4 text-gray-400 whitespace-nowrap">
                                    {{ new Date(entry.started_at).toLocaleDateString('it-IT', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </td>
                                <td class="px-5 py-4 text-right text-white font-bold whitespace-nowrap">
                                    {{ formatSeconds(entry.duration_seconds) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <!-- Error / empty state -->
        <div v-else class="text-center py-16 text-gray-500">
            <i class="fas fa-clock text-4xl mb-3 block text-[#07b4f6]"></i>
            Impossibile caricare il report. Controlla la connessione o l'intervallo selezionato.
        </div>
    </div>
</template>
