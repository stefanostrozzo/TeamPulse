<script setup>
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash-es';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({})
    },
    search: {
        type: String,
        default: ''
    },
    status: {
        type: String,
        default: 'all'
    },
    priority: {
        type: String,
        default: 'all'
    },
    sortField: {
        type: String,
        default: 'created_at'
    },
    sortDirection: {
        type: String,
        default: 'desc'
    }
});

const emit = defineEmits([
    'update:search',
    'update:status', 
    'update:priority',
    'update:sortField',
    'update:sortDirection',
    'update',
    'reset'
]);

// Stato locale
const localSearch = ref(props.search);
const localStatus = ref(props.status);
const localPriority = ref(props.priority);
const localSortField = ref(props.sortField);
const localSortDirection = ref(props.sortDirection);

// Opzioni dei filtri
const statusOptions = [
    { value: 'all', label: 'Tutti gli stati', icon: 'fas fa-layer-group' },
    { value: 'planning', label: 'In Pianificazione', icon: 'fas fa-map' },
    { value: 'active', label: 'Attivi', icon: 'fas fa-play-circle' },
    { value: 'paused', label: 'In Pausa', icon: 'fas fa-pause-circle' },
    { value: 'completed', label: 'Completati', icon: 'fas fa-check-circle' },
    { value: 'cancelled', label: 'Cancellati', icon: 'fas fa-times-circle' }
];

const priorityOptions = [
    { value: 'all', label: 'Tutte le priorità', icon: 'fas fa-flag' },
    { value: 'low', label: 'Bassa', icon: 'fas fa-flag', color: 'text-green-400' },
    { value: 'medium', label: 'Media', icon: 'fas fa-flag', color: 'text-yellow-400' },
    { value: 'high', label: 'Alta', icon: 'fas fa-flag', color: 'text-orange-400' },
    { value: 'urgent', label: 'Urgente', icon: 'fas fa-flag', color: 'text-red-400' }
];

const sortOptions = [
    { value: 'created_at', label: 'Data Creazione', icon: 'fas fa-calendar-plus' },
    { value: 'name', label: 'Nome', icon: 'fas fa-sort-alpha-down' },
    { value: 'end_date', label: 'Scadenza', icon: 'fas fa-clock' },
    { value: 'priority', label: 'Priorità', icon: 'fas fa-flag' },
    { value: 'progress', label: 'Progresso', icon: 'fas fa-chart-line' }
];

// Debounce per la ricerca
const debouncedEmit = debounce(() => {
    emit('update:search', localSearch.value);
    emitUpdate();
}, 300);

// Watchers per sincronizzazione props
watch(() => props.search, (val) => {
    localSearch.value = val;
});

watch(() => props.status, (val) => {
    localStatus.value = val;
});

watch(() => props.priority, (val) => {
    localPriority.value = val;
});

// Handler cambiamenti
const handleSearchInput = () => {
    debouncedEmit();
};

const handleStatusChange = () => {
    emit('update:status', localStatus.value);
    emitUpdate();
};

const handlePriorityChange = () => {
    emit('update:priority', localPriority.value);
    emitUpdate();
};

const handleSortChange = () => {
    emit('update:sortField', localSortField.value);
    emitUpdate();
};

const toggleSortDirection = () => {
    const newDirection = localSortDirection.value === 'asc' ? 'desc' : 'asc';
    localSortDirection.value = newDirection;
    emit('update:sortDirection', newDirection);
    emitUpdate();
};

const emitUpdate = () => {
    emit('update', {
        search: localSearch.value,
        status: localStatus.value,
        priority: localPriority.value,
        sortField: localSortField.value,
        sortDirection: localSortDirection.value
    });
};

const resetFilters = () => {
    localSearch.value = '';
    localStatus.value = 'all';
    localPriority.value = 'all';
    localSortField.value = 'created_at';
    localSortDirection.value = 'desc';
    
    emit('update:search', '');
    emit('update:status', 'all');
    emit('update:priority', 'all');
    emit('update:sortField', 'created_at');
    emit('update:sortDirection', 'desc');
    emit('reset');
};

// Computed per icone dinamiche
const sortDirectionIcon = computed(() => {
    return localSortDirection.value === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
});

const activeFiltersCount = computed(() => {
    let count = 0;
    if (localSearch.value) count++;
    if (localStatus.value !== 'all') count++;
    if (localPriority.value !== 'all') count++;
    if (localSortField.value !== 'created_at') count++;
    return count;
});
</script>

<template>
    <div class="project-filters bg-gray-800 rounded-xl border border-gray-700 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-filter mr-2 text-[#07b4f6]"></i>
                Filtri
                <span v-if="activeFiltersCount > 0" class="ml-2 bg-[#07b4f6] text-white text-xs px-2 py-1 rounded-full">
                    {{ activeFiltersCount }}
                </span>
            </h3>
            <button 
                @click="resetFilters"
                class="text-sm text-gray-400 hover:text-white transition-colors flex items-center"
                :class="{ 'text-[#07b4f6] hover:text-[#07b4f6]': activeFiltersCount > 0 }"
            >
                <i class="fas fa-redo mr-1"></i>
                Reset
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Ricerca -->
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-search mr-1"></i>
                    Cerca progetti
                </label>
                <div class="relative">
                    <input
                        v-model="localSearch"
                        type="text"
                        placeholder="Cerca per nome o descrizione..."
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg pl-10 pr-4 py-2 text-white placeholder-gray-400 focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] transition"
                        @input="handleSearchInput"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Stato -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-layer-group mr-1"></i>
                    Stato
                </label>
                <select
                    v-model="localStatus"
                    @change="handleStatusChange"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] transition"
                >
                    <option 
                        v-for="option in statusOptions" 
                        :key="option.value" 
                        :value="option.value"
                        class="bg-gray-700 text-white"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <!-- Priorità -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    <i class="fas fa-flag mr-1"></i>
                    Priorità
                </label>
                <select
                    v-model="localPriority"
                    @change="handlePriorityChange"
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] transition"
                >
                    <option 
                        v-for="option in priorityOptions" 
                        :key="option.value" 
                        :value="option.value"
                        :class="option.color"
                        class="bg-gray-700"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Ordinamento -->
        <div class="mt-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-sm font-medium text-gray-300">
                    <i class="fas fa-sort mr-1"></i>
                    Ordina per:
                </span>
                <select
                    v-model="localSortField"
                    @change="handleSortChange"
                    class="bg-gray-700 border border-gray-600 rounded-lg px-3 py-1 text-white text-sm focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] transition"
                >
                    <option 
                        v-for="option in sortOptions" 
                        :key="option.value" 
                        :value="option.value"
                        class="bg-gray-700"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <button
                    @click="toggleSortDirection"
                    class="p-2 text-gray-400 hover:text-white transition-colors"
                    :title="localSortDirection === 'asc' ? 'Crescente' : 'Decrescente'"
                >
                    <i :class="sortDirectionIcon"></i>
                </button>
            </div>

            <div class="text-xs text-gray-400">
                {{ localSortDirection === 'asc' ? 'Crescente' : 'Decrescente' }}
            </div>
        </div>
    </div>
</template>

<style scoped>
select option {
    background: #374151;
    color: white;
}

select option:hover {
    background: #07b4f6;
}
</style>