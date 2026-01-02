<script setup>
import { ref } from 'vue';

import ProjectStats from "@/Pages/Projects/Partials/ProjectStats.vue";
import ProjectCard from "@/Pages/Projects/Partials/ProjectCard.vue";
import EditProject from "@/Pages/Projects/Edit.vue";
import Modal from "@/Components/Items/Modal.vue";
import ShowElement from "@/Pages/Projects/ShowElement.vue";
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    projects: {
        type: [Array, Object],
        default: () => []
    },
    projectStats: {
        type: [Array, Object],
        default: () => []
    }
});

const isModalOpen = ref(false);
const selectedProject = ref(null);
const currentView = ref('grid');

const createProject = () => {
    selectedProject.value = null;
    isModalOpen.value = true;
};

const editProject = (project) => {
    selectedProject.value = project;
    isModalOpen.value = true;
};

const closeProjectModal = () => {
    isModalOpen.value = false;
};

const openDeleteConfirmation = (project) => {
    isModalOpen.value = false;

    Swal.fire({
        title: 'Sei sicuro?',
        text: `L'eliminazione di "${project.name}" è permanente.`,
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
            router.delete(route('project.destroy', project.id), {
                onSuccess: () => {
                    selectedProject.value = null;
                },
                preserveScroll: true,
            });
        }else if (result.dismiss === Swal.DismissReason.cancel) {
            editProject(project);
        }
    });
};

const handleOpenProject = (project) => {
    selectedProject.value = project;
    currentView.value = 'detail';
};

const backToGrid = () => {
    currentView.value = 'grid';
    selectedProject.value = null;
};

</script>

<template>
    <div class="space-y-10 pb-10">
        <Transition name="fade" mode="out-in">
            <div v-if="currentView === 'grid'">
                <div class="border-b border-gray-800 pb-5">
                    <h1 class="text-3xl font-bold text-white tracking-tight">I tuoi progetti</h1>
                    <ProjectStats
                        class="mt-4"
                        :stats="projectStats"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        @click="createProject"
                        class="group cursor-pointer border-2 border-dashed border-gray-700 rounded-xl flex flex-col items-center justify-center p-8 hover:border-[#07b4f6] hover:bg-[#07b4f6]/5 transition-all duration-300 min-h-[250px]"
                    >
                        <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center mb-4 group-hover:bg-[#07b4f6] transition-colors">
                            <i class="fas fa-plus text-gray-400 group-hover:text-white"></i>
                        </div>
                        <span class="text-lg font-medium text-gray-400 group-hover:text-white">Nuovo Progetto</span>
                    </div>

                    <ProjectCard
                        v-for="project in (projects.data || projects)"
                        :key="project.id"
                        :project="project"
                        @open="handleOpenProject"
                        @edit="editProject"
                    />
                </div>
            </div>

            <div v-else-if="currentView === 'detail' && selectedProject">
                <ShowElement
                    :project="selectedProject"
                    @back="backToGrid"
                />
            </div>

            <Modal :show="isModalOpen" @close="closeProjectModal" maxWidth="3xl">
                <div class="border border-gray-800 rounded-lg overflow-hidden shadow-xl">
                    <EditProject
                        :project="selectedProject"
                        @close="closeProjectModal"
                        @confirmDelete="openDeleteConfirmation"
                    />
                </div>
            </Modal>
        </Transition>
    </div>
</template>
