<script setup>
import { ref, watch, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { debounce } from 'lodash-es';
import ProjectCard from './Partials/ProjectCard.vue';
import ProjectFilters from './Partials/ProjectFilters.vue';
import ProjectStats from './Partials/ProjectStats.vue';

const props = defineProps({
    projects: Object,
    filters: Object,
    stats: Object
});

// Stato locale per i filtri
const localFilters = ref({
    search: props.filters.search || '',
    status: props.filters.status || 'all',
    priority: props.filters.priority || 'all',
    sort_field: props.filters.sort_field || 'created_at',
    sort_direction: props.filters.sort_direction || 'desc'
});

// Debounce per applicare i filtri
const debouncedApplyFilters = debounce(() => {
    router.get(route('dashboard', { tab: 'progetti', ...localFilters.value }), {}, {
        preserveState: true,
        replace: true,
    });
}, 500);

// Applica filtri
const applyFilters = () => {
    debouncedApplyFilters.cancel();
    router.get(route('dashboard', { tab: 'progetti', ...localFilters.value }), {}, {
        preserveState: true,
        replace: true,
    });
};

// Reset filtri
const resetFilters = () => {
    localFilters.value = {
        search: '',
        status: 'all',
        priority: 'all',
        sort_field: 'created_at',
        sort_direction: 'desc'
    };
    applyFilters();
};

// Watchers
watch(
    () => localFilters.value.search,
    () => {
        debouncedApplyFilters();
    }
);

watch(
    () => [localFilters.value.status, localFilters.value.priority, localFilters.value.sort_field, localFilters.value.sort_direction],
    () => {
        applyFilters();
    },
    { deep: true }
);

// Computed
const showingResults = computed(() => {
    const from = props.projects.from || 0;
    const to = props.projects.to || 0;
    const total = props.projects.total || 0;
    
    return `Mostrando ${from}-${to} di ${total} progetti`;
});

const hasActiveFilters = computed(() => {
    return localFilters.value.search !== '' || 
           localFilters.value.status !== 'all' || 
           localFilters.value.priority !== 'all';
});
</script>

<template>
    <div class="space-y-6">
        <!-- Statistiche -->
        <ProjectStats :stats="stats" />

        <!-- Filtri -->
        <ProjectFilters 
            v-model:search="localFilters.search"
            v-model:status="localFilters.status"
            v-model:priority="localFilters.priority"
            v-model:sort-field="localFilters.sort_field"
            v-model:sort-direction="localFilters.sort_direction"
            @reset="resetFilters"
        />

        <!-- Contenuto progetti -->
        <div v-if="projects.data.length > 0" class="flex justify-between items-center">
            <div class="text-sm text-gray-400">
                {{ showingResults }}
                <span v-if="hasActiveFilters" class="ml-2 text-[#07b4f6]">
                    • Filtri attivi
                </span>
            </div>
        </div>

        <!-- Grid Progetti -->
        <div v-if="projects.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <ProjectCard 
                v-for="project in projects.data"
                :key="project.id"
                :project="project"
                class="transition-all duration-300 hover:transform hover:scale-105"
            />
        </div>

        <!-- Stato vuoto -->
        <div v-else class="text-center py-16 bg-gray-800/30 rounded-xl border border-gray-700">
            <div class="max-w-md mx-auto">
                <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-[#07b4f6] to-cyan-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-project-diagram text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-300 mb-2">
                    {{ hasActiveFilters ? 'Nessun progetto corrisponde ai filtri' : 'Nessun progetto trovato' }}
                </h3>
                <p class="text-gray-500 mb-6">
                    {{ hasActiveFilters ? 'Prova a modificare i criteri di ricerca' : 'Inizia creando il tuo primo progetto' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <Link 
                        :href="route('progetti.create')" 
                        class="btn-primary inline-flex items-center justify-center"
                    >
                        <i class="fas fa-plus mr-2"></i>
                        Crea Progetto
                    </Link>
                    <button 
                        v-if="hasActiveFilters"
                        @click="resetFilters"
                        class="btn-secondary inline-flex items-center justify-center"
                    >
                        <i class="fas fa-redo mr-2"></i>
                        Reset Filtri
                    </button>
                </div>
            </div>
        </div>

        <!-- Paginazione -->
        <div v-if="projects.links.length > 3" class="flex justify-center pt-6 border-t border-gray-700">
            <div class="flex items-center space-x-2">
                <template v-for="(link, index) in projects.links" :key="index">
                    <Link 
                        v-if="link.url"
                        :href="route('dashboard', { tab: 'progetti', ...localFilters, page: link.url.split('page=')[1] })"
                        class="px-4 py-2 rounded-lg transition-all duration-200 border border-transparent min-w-[44px] flex items-center justify-center"
                        :class="{
                            'bg-[#07b4f6] text-white border-[#07b4f6] shadow-lg shadow-[#07b4f6]/20': link.active,
                            'bg-gray-700 text-gray-300 hover:bg-gray-600 hover:border-gray-500': !link.active && !link.url.includes('Previous') && !link.url.includes('Next'),
                            'bg-gray-800 text-gray-400 hover:bg-gray-700': link.url.includes('Previous') || link.url.includes('Next')
                        }"
                        v-html="link.label"
                        preserve-scroll
                    />
                    <span 
                        v-else 
                        class="px-4 py-2 text-gray-500"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-primary {
    @apply bg-gradient-to-r from-[#07b4f6] to-cyan-400 text-white px-4 py-2 rounded-lg font-medium hover:from-[#0599d4] hover:to-cyan-500 focus:outline-none focus:ring-2 focus:ring-[#07b4f6] focus:ring-opacity-50;
}

.btn-secondary {
    @apply bg-gray-700 text-gray-300 px-4 py-2 rounded-lg font-medium hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50;
}
</style>