<template>
    <div class="relative">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm"></i>
            <input 
                v-model="query" 
                type="text" 
                placeholder="Cerca un collega per nome o email..."
                class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg pl-9 pr-4 py-2 focus:ring-[#07b4f6] focus:border-[#07b4f6] transition-colors"
                @input="handleInput"
                @focus="showResults = true"
            >
            <i v-if="isLoading" class="fas fa-spinner fa-spin absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm"></i>
        </div>

        <!-- Dropdown Results -->
        <div v-if="showResults && (query.length >= 2 || results.length > 0)" 
             class="absolute z-20 w-full mt-2 bg-gray-800 border border-gray-700 rounded-lg shadow-xl max-h-60 overflow-y-auto">
            
            <div v-if="results.length === 0 && query.length >= 2 && !isLoading" class="p-3 text-sm text-center text-gray-400">
                Nessun utente trovato.
            </div>

            <ul v-else>
                <li v-for="user in results" :key="user.id" 
                    @click="selectUser(user)"
                    class="px-4 py-3 hover:bg-gray-700 cursor-pointer flex items-center border-b border-gray-700/50 last:border-0 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white text-xs font-bold mr-3 flex-shrink-0">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ user.name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ user.display_name.split(' (')[1]?.replace(')', '') || 'Nessun team' }}</p>
                    </div>
                </li>
            </ul>
        </div>
        
        <!-- Backdrop to close dropdown -->
        <div v-if="showResults" @click="showResults = false" class="fixed inset-0 z-10 pointer-events-auto" style="background: transparent;"></div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';

const emit = defineEmits(['contact-selected']);
const store = useMessagingStore();

const query = ref('');
const results = ref([]);
const isLoading = ref(false);
const showResults = ref(false);
let debounceTimer = null;

function handleInput() {
    clearTimeout(debounceTimer);
    
    if (query.value.trim().length < 2 && query.value !== ' ') {
        results.value = [];
        return;
    }

    isLoading.value = true;
    debounceTimer = setTimeout(async () => {
        results.value = await store.searchContacts(query.value);
        isLoading.value = false;
        showResults.value = true;
    }, 400); // 400ms debounce
}

function selectUser(user) {
    query.value = '';
    results.value = [];
    showResults.value = false;
    emit('contact-selected', user);
}
</script>
