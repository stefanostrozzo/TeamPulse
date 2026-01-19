<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import StatCharts from "./Partials/StatCharts.vue";
import ProjectPreviewCard from "./Partials/ProjectPreviewCard.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const homeData = computed(() => page.props.homeData);

const emit = defineEmits(['navigate-to-project','navigate-to-team']);

const handleProjectRedirect = (projectId) => {
    emit('navigate-to-project', projectId);
};
</script>

<template>
    <div class="space-y-8 pb-10">
        <div class="border-b border-gray-800 pb-6">
            <h1 class="text-3xl font-bold text-white">Benvenuto, {{ user?.name }}</h1>
            <p class="text-gray-400 mt-2">Riepilogo attività per il team corrente.</p>
        </div>

        <template v-if="homeData">
            <StatCharts
                :myTasksStats="homeData.myTasksStats"
                :tasksByPriority="homeData.tasksByPriority"
            />

            <div class="space-y-4">
                <h3 class="text-xl font-bold text-white">I miei Progetti</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <ProjectPreviewCard
                        v-for="project in homeData.projectPreviews"
                        :key="project.id"
                        :project="project"
                        @open-project="handleProjectRedirect"
                    />
                </div>
            </div>

            <div v-if="homeData.isManager" class="bg-gray-900 border border-[#07b4f6]/20 p-6 rounded-2xl flex justify-between items-center">
                <div>
                    <h4 class="text-white font-bold text-lg">Gestione Team</h4>
                    <p class="text-gray-400 text-sm">Puoi amministrare i membri di questo spazio di lavoro.</p>
                </div>
                <button @click="goToTeams" class="px-6 py-2 bg-[#07b4f6] text-white font-bold rounded-xl shadow-lg shadow-[#07b4f6]/20">
                    Gestisci Team
                </button>
            </div>
        </template>

        <div v-else class="text-center py-20 bg-gray-900/50 rounded-3xl border border-gray-800">
            <p class="text-gray-400">Nessun dato disponibile. Crea un team per iniziare.</p>
        </div>
    </div>
</template>
