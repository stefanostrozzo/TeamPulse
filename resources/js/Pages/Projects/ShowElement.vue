<script setup>
import { ref } from 'vue';
import Swal from 'sweetalert2';

import TaskList from '@/Pages/Projects/Tasks/TaskList.vue';
import Modal from "@/Components/Items/Modal.vue";
import TaskForm from "@/Pages/Projects/Tasks/TaskForm.vue";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    project: Object,
    tasks: Array
});

const emit = defineEmits(['back']);
const activeTab = ref('elenco');

const isModalOpen = ref(false);
const selectedTask = ref(null);

const openCreateTask = () => {
    selectedTask.value = null;
    isModalOpen.value = true;
};

const closeTask = () => {
    isModalOpen.value = false;
}

const editTask = (task) => {
    selectedTask.value = task;
    activeTab.value = task;
}

const openDeleteConfirmation = (task) => {
    isModalOpen.value = false;

    Swal.fire({
        title: 'Sei sicuro?',
        text: `L'eliminazione di "${task.name}" è permanente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Sì, elimina',
        cancelButtonText: 'Annulla',
        background: '#1f2937',
        color: '#ffffff',
    }).then((result) => {
        if (result.isConfirmed) {
           //TODO
        }else if (result.dismiss === Swal.DismissReason.cancel) {
            editTask(task);
        }
    });
};

const PROJECT_STATUSES = {
    'active': { label: 'Attivo', color: 'bg-green-500' },
    'completed': { label: 'Completato', color: 'bg-blue-500' },
    'on-hold': { label: 'In attesa', color: 'bg-yellow-500' },
    'archived': { label: 'Archiviato', color: 'bg-gray-500' }
};

const PROJECT_PRIORITIES = {
    'low': { label: 'Bassa', color: 'text-green-400' },
    'medium': { label: 'Media', color: 'text-yellow-400' },
    'high': { label: 'Alta', color: 'text-red-400' }
};

</script>

<template>
    <div class="animate-in fade-in duration-500">
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div class="flex items-center space-x-4">
                    <button @click="$emit('back')" class="p-2 hover:bg-gray-800 rounded-full text-gray-400 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ project.name }}</h1>
                        <p class="text-gray-400 text-sm mt-1">{{ project.description }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 md:justify-end">
                    <div class="flex items-center px-3 py-1 bg-gray-800 border border-gray-700 rounded-full">
                        <div class="w-2 h-2 rounded-full mr-2" :class="PROJECT_STATUSES[project.status]?.color"></div>
                        <span class="text-xs font-medium text-gray-300 uppercase tracking-wider">
                            {{ PROJECT_STATUSES[project.status]?.label || project.status }}
                        </span>
                    </div>

                    <div class="flex items-center px-3 py-1 bg-gray-800 border border-gray-700 rounded-full">
                        <i class="fas fa-flag text-[10px] mr-2" :class="PROJECT_PRIORITIES[project.priority]?.color"></i>
                        <span class="text-xs font-medium uppercase tracking-wider" :class="PROJECT_PRIORITIES[project.priority]?.color">
                            {{ PROJECT_PRIORITIES[project.priority]?.label || project.priority }}
                        </span>
                    </div>

                    <div class="flex items-center px-3 py-1 bg-gray-800 border border-gray-700 rounded-full text-gray-400">
                        <i class="far fa-calendar-alt text-xs mr-2"></i>
                        <span class="text-xs font-medium uppercase tracking-wider">
                            {{ project.end_date ? new Date(project.end_date).toLocaleDateString('it-IT') : 'Nessuna Scadenza' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <button
            @click="openCreateTask"
            class="bg-[#07b4f6] hover:bg-[#06a3de] text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center shadow-lg shadow-[#07b4f6]/20 transition-all active:scale-95"
        >
            <i class="fas fa-plus mr-2"></i>
            Nuova Task
        </button>
        <div class="flex space-x-8 border-b border-gray-800 mt-6 mb-6">
            <button
                v-for="tab in ['elenco', 'kanban', 'gantt']"
                :key="tab"
                @click="activeTab = tab"
                :class="['pb-4 text-sm font-medium capitalize transition-all relative',
                         activeTab === tab ? 'text-[#07b4f6]' : 'text-gray-500 hover:text-gray-300']"
            >
                {{ tab }}
                <div v-if="activeTab === tab" class="absolute bottom-0 left-0 w-full h-0.5 bg-[#07b4f6]"></div>
            </button>
        </div>

        <div class="min-h-100">

            <!--List-->
            <div v-if="activeTab === 'elenco'" class="fade-in">
                <TaskList
                    :tasks="project.tasks"
                />
            </div>

            <div class="relative min-h-screen overflow-x-hidden">
                <div :class="['transition-all duration-500', isModalOpen ? 'mr-[35%]' : '']">
                </div>

                <div v-if="isModalOpen"
                     @click="closeTask"
                     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 transition-opacity">
                </div>

                <div :class="[
                    'fixed top-0 right-0 h-full w-[35%] bg-gray-900 border-l border-gray-800 z-50 transform transition-transform duration-300 ease-in-out shadow-2xl overflow-y-auto',
                    isModalOpen ? 'translate-x-0' : 'translate-x-full'
                ]">
                    <TaskForm
                        v-if="isModalOpen"
                        :project="props.project"
                        :task="selectedTask"
                        @close="closeTask"
                        @confirmDelete="openDeleteConfirmation"
                    />
                </div>
            </div>

            <!--Kanban Section-->
            <div v-if="activeTab === 'kanban'" class="text-gray-500 italic text-center py-20">
                In arrivo...
            </div>

            <!--Gantt Section-->
            <div v-if="activeTab === 'gantt'" class="text-gray-500 italic text-center py-20">
                In arrivo...
            </div>
        </div>
    </div>
</template>
