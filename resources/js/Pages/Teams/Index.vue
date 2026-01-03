<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Modal from "@/Components/Items/Modal.vue";

// Import management components (to be created or adapted)
import EditTeam from "./Partials/EditTeam.vue";
import TeamCard from "./Partials/TeamCard.vue";
import ShowTeamMembers from "./Partials/ShowMembers.vue";

/**
 * Component Props
 * @property {Array} teams - List of teams the user belongs to
 * @property {Boolean} autoOpenCreate - Flag to trigger the creation modal on load
 */
const props = defineProps({
    teams: { type: Array, default: () => [] },
    autoOpenCreate: { type: Boolean, default: false }
});

const page = usePage();
// Track the currently active team from the auth user state
const currentTeamId = ref(page.props.auth.user.current_team_id);

const isModalOpen = ref(false);
const selectedTeam = ref(null);
const currentView = ref('grid'); // Toggles between 'grid' list and 'detail' view

/**
 * Open modal to create a new team
 */
const createTeam = () => {
    selectedTeam.value = null;
    isModalOpen.value = false;

    nextTick(() => {
        isModalOpen.value = true;
    });
};

/**
 * Open modal to edit an existing team
 * Typically triggered by right-click (contextmenu)
 */
const editTeam = (team) => {
    selectedTeam.value = team;
    isModalOpen.value = false;

    nextTick(() => {
        isModalOpen.value = true;
    });
};

/**
 * Close the team modal and reset selection
 */
const closeTeamModal = () => {
    isModalOpen.value = false;
};

/**
 * Switch the active team context
 * Sends a POST request to update the user's current_team_id
 */
const selectTeam = (teamId) => {
    router.post(route('teams.switch', teamId), {}, {
        onSuccess: () => {
            currentTeamId.value = teamId;
        },
        preserveScroll: true,
    });
};

/**
 * Enter the detailed view of a specific team (members and roles)
 */
const openTeamDetail = (team) => {
    selectedTeam.value = team;
    currentView.value = 'detail';
};

/**
 * Return to the main teams grid
 */
const backToGrid = () => {
    currentView.value = 'grid';
    selectedTeam.value = null;
};

/**
 * Handle initial redirection logic if passed from the Dashboard
 */
onMounted(() => {
    if (props.autoOpenCreate) createTeam();
});
</script>

<template>
    <div class="space-y-10 pb-10">
        <Transition name="fade" mode="out-in">
            <div v-if="currentView === 'grid'">
                <div class="border-b border-gray-800 pb-5">
                    <h1 class="text-3xl font-bold text-white tracking-tight">I tuoi Team</h1>
                    <p class="text-gray-400 mt-2">Seleziona uno spazio di lavoro per vedere i relativi progetti.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                    <div
                        @click="createTeam"
                        class="group cursor-pointer border-2 border-dashed border-gray-700 rounded-xl flex flex-col items-center justify-center p-8 hover:border-[#07b4f6] hover:bg-[#07b4f6]/5 transition-all duration-300 min-h-[250px]"
                    >
                        <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center mb-4 group-hover:bg-[#07b4f6] transition-colors">
                            <i class="fas fa-plus text-gray-400 group-hover:text-white"></i>
                        </div>
                        <span class="text-lg font-medium text-gray-400 group-hover:text-white">Nuovo Team</span>
                    </div>

                    <TeamCard
                        v-for="team in teams"
                        :key="team.id"
                        :team="team"
                        :is-current="team.id === currentTeamId"
                        @select="selectTeam"
                        @edit="editTeam"
                        @open="openTeamDetail"
                    />
                </div>
            </div>

            <div v-else-if="currentView === 'detail' && selectedTeam">
                <ShowTeamMembers
                    :team="selectedTeam"
                    @back="backToGrid"
                />
            </div>
        </Transition>
        <Modal :show="isModalOpen" @close="closeTeamModal" maxWidth="3xl">
            <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden shadow-xl p-6">
                <EditTeam
                    :team="selectedTeam"
                    @close="closeTeamModal"
                />
            </div>
        </Modal>
    </div>
</template>
