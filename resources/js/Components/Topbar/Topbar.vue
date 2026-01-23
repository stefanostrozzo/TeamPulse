<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const emit = defineEmits(['toggle-notifications', 'search-navigation']);

const searchQuery = ref(null);
const results = ref([]);
const isDropdownOpen = ref(false);

/**
 * Funzione per gestire la navigazione al click del risultato
 */
const handleSelect = (item) => {
    emit('search-navigation', {
        type: item.type,
        id: item.id,
        projectId: item.project_id || null
    });

    searchQuery.value = '';
    isDropdownOpen.value = false;
};

// Esempio di chiamata API con debounce (usa lodash o un timeout semplice)
let searchTimeout;
watch(searchQuery, (val) => {
    clearTimeout(searchTimeout);
    if (val.length < 2) {
        results.value = [];
        isDropdownOpen.value = false;
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await axios.get(`/search/global?q=${val}`);
            const data = response.data;

            const flatResults = [
                ...(data.projects || []).map(i => ({ ...i, type: 'project', type_label: 'PROGETTI' })),
                ...(data.tasks || []).map(i => ({ ...i, type: 'task', type_label: 'TASK' })),
                ...(data.teams || []).map(i => ({ ...i, type: 'team', type_label: 'TEAM' })),
                ...(data.members || []).map(i => ({ ...i, type: 'member', type_label: 'MEMBRI' })),
            ];

            results.value = flatResults;
            isDropdownOpen.value = flatResults.length > 0;

        } catch (e) {
            console.error("Errore ricerca:", e);
        }
    }, 300);
});
</script>

<template>
    <div class="topbar bg-gray-800 border-b border-gray-700 px-6 pt-5 pb-3 flex items-center justify-between relative">
        <div class="search-container relative w-full max-w-xl">
            <span class="search-icon absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 104.243 11.964l4.271 4.272a.75.75 0 101.061-1.06l-4.272-4.272A6.75 6.75 0 0010.5 3.75zM5.25 10.5a5.25 5.25 0 1110.5 0 5.25 5.25 0 01-10.5 0z" clip-rule="evenodd" />
                </svg>
            </span>
            <input
                v-model="searchQuery"
                type="text"
                class="search-input w-full bg-gray-700 rounded-xl pl-11 pr-4 py-2 text-sm text-white border-transparent focus:border-[#07b4f6] focus:ring-0 transition-all"
                placeholder="Cerca qualcosa..."
            />

            <div v-if="isDropdownOpen && results.length > 0" class="absolute top-full left-0 w-full mt-2 bg-gray-900 border border-gray-700 rounded-xl shadow-2xl z-50 overflow-hidden">
                <div v-for="item in results" :key="item.type + '-' + item.id"
                     @click="handleSelect(item)"
                     class="flex justify-between items-center px-4 py-3 hover:bg-gray-800 cursor-pointer group transition-colors">

                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-[#07b4f6]"></div>
                        <span class="text-sm text-gray-200 group-hover:text-white">{{ item.name ?? item.title }}</span>
                    </div>

                    <span class="text-[10px] text-gray-500 uppercase font-bold">
                        {{ item.type_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
