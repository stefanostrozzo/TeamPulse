<script setup>
import { ref } from 'vue';

const props = defineProps({
    project: Object
});

const emit = defineEmits(['back']);
const activeTab = ref('generale');
</script>

<template>
    <div class="animate-in fade-in duration-500">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <button @click="$emit('back')" class="p-2 hover:bg-gray-800 rounded-full text-gray-400 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1 class="text-3xl font-bold text-white">{{ project.name }}</h1>
            </div>
        </div>

        <div class="flex space-x-8 border-b border-gray-800 mb-6">
            <button
                v-for="tab in ['generale', 'kanban', 'gantt']"
                :key="tab"
                @click="activeTab = tab"
                :class="['pb-4 text-sm font-medium capitalize transition-all relative',
                         activeTab === tab ? 'text-[#07b4f6]' : 'text-gray-500 hover:text-gray-300']"
            >
                {{ tab }}
                <div v-if="activeTab === tab" class="absolute bottom-0 left-0 w-full h-0.5 bg-[#07b4f6]"></div>
            </button>
        </div>

        <div class="min-h-[500px]">
            <div v-if="activeTab === 'kanban'" class="text-white">
                <p class="text-gray-500 italic">Kanban in arrivo...</p>
            </div>
            <div v-if="activeTab === 'gantt'" class="text-white">
                <p class="text-gray-500 italic">Gantt in arrivo...</p>
            </div>
        </div>
    </div>
</template>
