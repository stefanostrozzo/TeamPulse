<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const emit = defineEmits(['close', 'updated']);
const page = usePage();
const isManager = computed(() => page.props.isManager ?? false);

const types = ref([]);
const loading = ref(false);
const newName = ref('');
const newColor = ref('#6366f1');
const editingId = ref(null);
const editName = ref('');
const editColor = ref('');
const error = ref('');

const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
};

async function loadTypes() {
    loading.value = true;
    try {
        const res = await fetch(route('task-types.index'), { headers });
        types.value = await res.json();
    } finally { loading.value = false; }
}

async function addType() {
    error.value = '';
    if (!newName.value.trim()) return;
    const res = await fetch(route('task-types.store'), {
        method: 'POST', headers,
        body: JSON.stringify({ name: newName.value.trim(), color: newColor.value }),
    });
    if (!res.ok) { error.value = (await res.json()).message ?? 'Errore.'; return; }
    newName.value = ''; newColor.value = '#6366f1';
    await loadTypes(); emit('updated');
}

async function saveEdit(type) {
    error.value = '';
    const res = await fetch(route('task-types.update', type.id), {
        method: 'PUT', headers,
        body: JSON.stringify({ name: editName.value, color: editColor.value }),
    });
    if (!res.ok) { error.value = (await res.json()).message ?? 'Errore.'; return; }
    editingId.value = null;
    await loadTypes(); emit('updated');
}

async function deleteType(type) {
    if (!confirm(`Eliminare "${type.name}"? Le task associate perderanno il tipo.`)) return;
    await fetch(route('task-types.destroy', type.id), { method: 'DELETE', headers });
    await loadTypes(); emit('updated');
}

onMounted(loadTypes);
</script>

<template>
    <div class="bg-gray-900 rounded-2xl border border-gray-700 p-6 w-full max-w-md shadow-2xl">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fas fa-tags text-[#07b4f6]"></i> Tipologie Task
            </h2>
            <button @click="emit('close')" class="text-gray-500 hover:text-white transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <p v-if="error" class="text-red-400 text-xs mb-3">{{ error }}</p>

        <div v-if="loading" class="text-center text-gray-500 py-4">
            <i class="fas fa-spinner fa-spin"></i>
        </div>
        <ul v-else class="space-y-2 mb-5">
            <li v-for="type in types" :key="type.id"
                class="flex items-center gap-3 bg-gray-800 rounded-xl px-4 py-2.5">
                <template v-if="editingId === type.id && isManager">
                    <input type="color" v-model="editColor" class="w-7 h-7 rounded cursor-pointer border-0 bg-transparent" />
                    <input v-model="editName"
                           class="flex-1 bg-gray-700 text-white text-sm rounded-lg px-3 py-1.5 border border-gray-600 focus:outline-none focus:border-[#07b4f6]" />
                    <button @click="saveEdit(type)" class="text-emerald-400 text-xs font-bold">Salva</button>
                    <button @click="editingId = null" class="text-gray-500 text-xs">Annulla</button>
                </template>
                <template v-else>
                    <span class="w-3.5 h-3.5 rounded-full flex-shrink-0" :style="{ backgroundColor: type.color }"></span>
                    <span class="flex-1 text-sm text-white">{{ type.name }}</span>
                    <template v-if="isManager">
                        <button @click="editingId = type.id; editName = type.name; editColor = type.color"
                                class="text-gray-400 hover:text-[#07b4f6] transition text-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button @click="deleteType(type)"
                                class="text-gray-400 hover:text-red-400 transition text-xs">
                            <i class="fas fa-trash"></i>
                        </button>
                    </template>
                </template>
            </li>
            <li v-if="!types.length" class="text-gray-500 text-sm text-center py-2">
                Nessuna tipologia configurata.
            </li>
        </ul>

        <div v-if="isManager"
             class="flex items-center gap-3 bg-gray-800 rounded-xl px-4 py-3 border border-gray-700">
            <input type="color" v-model="newColor" class="w-7 h-7 rounded cursor-pointer border-0 bg-transparent" />
            <input v-model="newName" @keyup.enter="addType"
                   placeholder="Nuova tipologia..."
                   class="flex-1 bg-gray-700 text-white text-sm rounded-lg px-3 py-1.5 border border-gray-600 focus:outline-none focus:border-[#07b4f6]" />
            <button @click="addType"
                    class="bg-[#07b4f6] hover:bg-[#069acc] text-white text-xs font-bold px-3 py-2 rounded-lg transition">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <p v-else class="text-xs text-gray-500 text-center mt-2 pt-2 border-t border-gray-700">
            Solo i manager possono gestire le tipologie.
        </p>
    </div>
</template>
